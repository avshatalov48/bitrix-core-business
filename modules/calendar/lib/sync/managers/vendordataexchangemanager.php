<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Handlers\UpdateMasterExdateHandler;
use Bitrix\Calendar\Core\Managers\Compare\EventCompareManager;
use Bitrix\Calendar\Internals\FlagRegistry;
use Bitrix\Calendar\Internals\HandleStatusTrait;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Exceptions\AuthException;
use Bitrix\Calendar\Sync\Exceptions\RemoteAccountException;
use Bitrix\Calendar\Sync\Handlers\MasterPushHandler;
use Bitrix\Calendar\Sync\Handlers\SyncEventMergeHandler;
use Bitrix\Calendar\UserField\ResourceBooking;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Uri;
use CCalendar;
use CCalendarSect;
use Exception;

class VendorDataExchangeManager
{
	use HandleStatusTrait;

	private static array $outgoingManagersCache = [];

	protected Sync\Entities\SyncEventMap $syncEventMap;
	protected SyncEventMergeHandler $handlerMerge;
	/**
	 * true if this connection has sync token before sync start
	 *
	 * @var bool
	 */
	protected bool $isFullSync = false;

	private Sync\Factories\SyncEventFactory $syncEventFactory;
	private Core\Mappers\EventConnection $eventConnectionMapper;
	private Core\Mappers\Event $eventMapper;
	private Core\Mappers\Section $sectionMapper;
	private Core\Mappers\SectionConnection $sectionConnectionMapper;
	protected Sync\Factories\FactoryBase $factory;
	protected Sync\Entities\SyncSectionMap $syncSectionMap;
	protected array $importedLocalEventUidList = [];

	/**
	 * @throws ObjectNotFoundException
	 */
	public function __construct(Sync\Factories\FactoryBase $factory, Sync\Entities\SyncSectionMap $syncSectionMap)
	{
		$this->factory = $factory;
		$this->syncSectionMap = $syncSectionMap;
		$this->isFullSync = !$this->factory->getConnection()->getToken();

		/** @var Core\Mappers\Factory $mapperHelper */
		$mapperHelper = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$this->sectionConnectionMapper = $mapperHelper->getSectionConnection();
		$this->sectionMapper = $mapperHelper->getSection();
		$this->eventConnectionMapper = $mapperHelper->getEventConnection();
		$this->eventMapper = $mapperHelper->getEvent();
		$this->syncEventFactory = new Sync\Factories\SyncEventFactory();


		$handlerMergeClass = Core\Handlers\HandlersMap::getHandler('syncEventMergeHandler');
		$this->handlerMerge = new $handlerMergeClass();
	}

	/**
	 * @return $this
	 *
	 * @throws Core\Base\BaseException
	 * @throws ArgumentException
	 * @throws ObjectNotFoundException
	 * @throws Exception
	 */
	public function exchange(): self
	{
		$pushManager = new PushManager();
		$push = $pushManager->getPush(PushManager::TYPE_CONNECTION, $this->factory->getConnection()->getId());
		// TODO: what could to do, if push is blocked ?
		$pushManager->setBlockPush($push);
		try
		{
			$this->exchangeSections();

			$this->sendResult(MasterPushHandler::MASTER_STAGE[1]);

			$this->blockSectionPush($this->syncSectionMap);
			try
			{
				$this->exchangeEvents();
			}
			catch(BaseException $e)
			{}
			finally
			{
				$this->unblockSectionPush($this->syncSectionMap);
			}

			$this->sendResult(MasterPushHandler::MASTER_STAGE[2]);
			$this->sendResult(MasterPushHandler::MASTER_STAGE[3]);

			$this
				->updateConnection($this->factory->getConnection())
				->clearCache() // Clear legacy cache after whole sync
			;
		}
		catch(BaseException $e)
		{
		}
		finally
		{
			$pushManager->setUnblockPush($push);
		}

		return $this;
	}

	private function sendResult(string $stage): void
	{
		$this->sendStatus([
			'vendorName'  => $this->factory->getConnection()->getVendor()->getCode(),
			'accountName' => $this->factory->getConnection()->getName(),
			'stage'       => $stage,
		]);
	}

	public function clearCache(): void
	{
		CCalendar::ClearCache();
	}

	/**
	 * @return self
	 *
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws LoaderException
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function importEvents(): self
	{
		$importEventManager = (new ImportEventManager($this->factory, $this->syncSectionMap));

		$syncEventMap = $importEventManager->import()->getEvents();

		$this->prepareLocalSyncEventMapWithVendorEventId($syncEventMap);
		$this->handleEventsToLocalStorage($syncEventMap);
		$this->handleSectionsToLocalStorage($this->syncSectionMap);

		return $this;
	}

	/**
	 * @return $this
	 *
	 * @throws ArgumentException
	 * @throws ObjectNotFoundException
	 * @throws Exception
	 * @throws RemoteAccountException
	 * @throws AuthException
	 */
	public function importSections(): self
	{
		//sections
		$sectionImporter = (new ImportSectionManager($this->factory))->import();

		$this->handleImportedSections($sectionImporter->getSyncSectionMap());

		return $this;
	}

	/**
	 * @param Connection $connection
	 * @return self
	 * @throws BaseException
	 */
	public function updateConnection(Connection $connection): self
	{
		$connection->setLastSyncTime(new Core\Base\Date());
		(new Core\Mappers\Connection())->update($connection);

		$accountType = $connection->getAccountType() === Sync\Google\Helper::GOOGLE_ACCOUNT_TYPE_API
			? 'google'
			: $connection->getAccountType()
		;

		Util::addPullEvent('refresh_sync_status', $connection->getOwner()->getId(), [
			'syncInfo' => [
				$accountType => [
					'status' => $this->getSyncStatus($connection->getStatus()),
					'type' => $accountType,
					'connected' => true,
					'id' => $connection->getId(),
					'syncOffset' => 0,
				],
			],
			'requestUid' => Util::getRequestUid(),
		]);

		return $this;
	}

	private function getSyncStatus(?string $status): bool
	{
		return $status && preg_match("/^\[(2\d\d|0)\][a-z0-9 _]*/i", $status);
	}

	/**
	 * @param Sync\Entities\SyncSectionMap $externalSyncSectionMap
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws BaseException
	 */
	public function handleImportedSections(Sync\Entities\SyncSectionMap $externalSyncSectionMap): void
	{
		$this->mergeSyncedSyncSectionsWithSavedSections($externalSyncSectionMap);
		$this->handleSectionsToLocalStorage($externalSyncSectionMap);
		$this->removeDeletedExternalSections($externalSyncSectionMap);
	}

	/**
	 * @param Sync\Entities\SyncEvent $syncEvent
	 * @param string|null $key
	 * @param Sync\Entities\SyncEvent|null $masterSyncEvent
	 *
	 * @return void
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws SystemException
	 */
	public function handleSyncEvent(
		Sync\Entities\SyncEvent $syncEvent,
		?string $key = null,
		?Sync\Entities\SyncEvent $masterSyncEvent = null
	): void
	{
		if ($syncEvent->getEventConnection() === null)
		{
			return;
		}

		if ($masterSyncEvent !== null)
		{
			$syncEvent->getEvent()->setRecurrenceId($masterSyncEvent->getId());
		}

		$this->mergeExternalEventWithLocalParams($syncEvent);

		if ($syncEvent->getAction() === Sync\Dictionary::SYNC_EVENT_ACTION['delete'])
		{
			//if we drag out an old event that has not been saved in our system
			if ($syncEvent->getEvent()->getId() === null)
			{
				if ($syncEvent->isInstance())
				{
					if ($masterSyncEvent === null)
					{
						$masterSyncEvent = $this->getMasterSyncEvent($syncEvent);
					}
					if (!$masterSyncEvent)
					{
						return;
					}

					if ($masterSyncEvent->getAction() === Sync\Dictionary::SYNC_EVENT_ACTION['delete'])
					{
						return;
					}

					if ($masterSyncEvent->getId() === null)
					{
						$this->handleSyncEvent($masterSyncEvent);

						return;
					}

					$this->updateMasterExdate($this->addExdateToMasterEvent($masterSyncEvent, $syncEvent));
				}

				return;
			}

			$this->deleteEvent($syncEvent);

			return;
		}

		$this->saveEvent($syncEvent);

		if (
			$masterSyncEvent
			&& $masterSyncEvent->getId()
			&& $syncEvent->isInstance()
		)
		{
			$this->updateMasterExdate($this->addExdateToMasterEvent($masterSyncEvent, $syncEvent));
		}
	}

	/**
	 * @param Sync\Entities\SyncSectionMap $externalSyncSectionMap
	 * @param Sync\Entities\SyncSection $syncSection
	 * @return bool
	 */
	public function filterUnchangedSections(
		Sync\Entities\SyncSectionMap $externalSyncSectionMap,
		Sync\Entities\SyncSection $syncSection
	): bool
	{
		/** @var Sync\Entities\SyncSection $externalSyncSection */
		if (
			($externalSyncSection = $externalSyncSectionMap->getItem($syncSection->getSectionConnection()
				->getVendorSectionId()))
			&& $syncSection->getSectionConnection()->getVersionId() !== $externalSyncSection->getSectionConnection()
				->getVersionId()
		)
		{
			$syncSection->getSectionConnection()->setVersionId($externalSyncSection->getSectionConnection()->getVersionId());

			return true;
		}

		return false;
	}

	/**
	 * @param Sync\Entities\SyncEvent $syncEvent
	 *
	 * @return void
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws SystemException
	 */
	public function handleDeleteInstance(Sync\Entities\SyncEvent $syncEvent): void
	{
		// $event = $syncEvent->getEvent();
		$masterSyncEvent = $this->getMasterSyncEvent($syncEvent);
		if (!$masterSyncEvent)
		{
			return;
		}

		$this->prepareExcludedDatesMasterEvent(
			$masterSyncEvent,
			$syncEvent->getEvent()->getOriginalDateFrom()
		);

		if ($masterSyncEvent->getId() === null)
		{
			//todo look log and check scenario
			AddMessage2Log('Master event has not id. instance id = ' . $syncEvent->getVendorEventId());
			return;
		}

		$this->updateMasterExdate($masterSyncEvent->getEvent());
		$masterSyncEvent->setEvent($masterSyncEvent->getEvent());

		$this->syncEventMap->updateItem($masterSyncEvent, $masterSyncEvent->getVendorEventId());
	}

	/**
	 * @param Core\Role\Role $owner
	 * @return Core\Event\Properties\MeetingDescription
	 */
	public function getMeetingDescriptionForNewEvent(Core\Role\Role $owner): Core\Event\Properties\MeetingDescription
	{
		return (new Core\Event\Properties\MeetingDescription())
			->setHostName($owner->getFullName())
			->setMeetingCreator($owner->getId())
			->setReInvite(false)
			->setLanguageId(($owner->getRoleEntity() instanceof Core\Role\User)
				? $owner->getRoleEntity()->getLanguageId()
				: LANGUAGE_ID
			)
			->setIsNotify(true)
			->setAllowInvite(true)
		;
	}

	/**
	 * @param Sync\Entities\SyncEvent $syncEvent
	 *
	 * @return Event
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws SystemException
	 */
	public function deleteEvent(Sync\Entities\SyncEvent $syncEvent): Event
	{
		$event = $syncEvent->getEvent();

		// todo handle meeting status
		if ($event->isInstance())
		{
			$this->handleDeleteInstance($syncEvent);
		}

		if ($event->isRecurrence())
		{
			$this->handleDeleteRecurrenceEvent($syncEvent);
		}

		if ($event->isSingle())
		{
			$this->handleDeleteSingleEvent($syncEvent);
		}

		$this->eventMapper->delete(
			$event,
			[
				'softDelete' => true,
				'originalFrom' => $syncEvent->getEventConnection()->getConnection()->getVendor()->getCode(),
			]
		);

		$this->eventConnectionMapper->delete($syncEvent->getEventConnection());

		return $event;
	}

	/**
	 * @param Sync\Entities\SyncEvent $syncEvent
	 *
	 * @return void
	 * @throws ArgumentException
	 */
	public function saveEvent(Sync\Entities\SyncEvent $syncEvent): void
	{
		$event = $syncEvent->getEvent();
		$event = $event->isNew()
			? $this->eventMapper->create($event, [
				'originalFrom' => $this->factory->getServiceName(),
			])
			: $this->eventMapper->update($event, [
				'originalFrom' => $this->factory->getServiceName(),
			])
		;

		if ($event)
		{
			$syncEvent->setEvent($event);
			$eventConnection = $syncEvent->getEventConnection();
			if ($eventConnection)
			{
				$eventConnection
					->setEvent($event)
					->setVersion($event->getVersion())
				;

				$eventConnection->getId()
					? $this->eventConnectionMapper->update($eventConnection)
					: $this->eventConnectionMapper->create($eventConnection)
				;
			}
		}
	}

	/**
	 * @param Sync\Entities\SyncEventMap $externalEventMap
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws LoaderException
	 * @throws SystemException
	 */
	public function handleImportedEvents(Sync\Entities\SyncEventMap $externalEventMap): void
	{
		$this->prepareLocalSyncEventMapWithVendorEventId($externalEventMap);
		$this->handleEventsToLocalStorage($externalEventMap);
	}

	/**
	 * @param Sync\Entities\SyncSection $syncSection
	 * @return void
	 */
	public function savePermissions(Sync\Entities\SyncSection $syncSection): void
	{
		CCalendarSect::SavePermissions(
			$syncSection->getSection()->getId(),
			CCalendarSect::GetDefaultAccess(
				$syncSection->getSection()->getType(),
				$syncSection->getSection()->getOwner()->getId()
			)
		);
	}

	/**
	 * @return void
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws ObjectNotFoundException
	 * @throws Exception
	 */
	private function exchangeSections(): void
	{
		//sections
		$sectionImporter = (new ImportSectionManager($this->factory))->import();

		$sectionExporter = (new OutgoingSectionManager(
			$this->factory,
			$this->prepareSyncSectionBeforeExport(
				$this->getFilteredSectionMapForExport($sectionImporter->getSyncSectionMap())
			)
		))->export();

		$this->handleImportedSections($sectionImporter->getSyncSectionMap());
		$this->handleExportedSections($sectionExporter->getSyncSectionMap());

		$this->filterBrokenSyncSections();
	}

	/**
	 * @param Sync\Entities\SyncSectionMap $syncSectionMap
	 * @return void
	 * @throws ArgumentException
	 * @throws BaseException
	 */
	private function handleExportedSections(Sync\Entities\SyncSectionMap $syncSectionMap): void
	{
		$this->handleSectionsToLocalStorage($syncSectionMap);
	}

	/**
	 * @return void
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws LoaderException
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function exchangeEvents(): void
	{
		// $syncSectionForImport = $this->isFullSync ? $this->syncSectionMap : $this->importedSyncSectionMap;
		$eventImporter = (new ImportEventManager($this->factory, $this->syncSectionMap))->import();
		$this->handleImportedEvents($eventImporter->getEvents());

		$savedSyncEventMap = $this->getLocalEventsForExport();

		if ($savedSyncEventMap)
		{
			(new ExportEventManager($this->factory, $this->syncSectionMap))->export($savedSyncEventMap);
			$this->updateExportedEvents($savedSyncEventMap);
		}

		// update tokens for sections
		$this->handleSectionsToLocalStorage($this->syncSectionMap);
	}

	/**
	 * @param Sync\Entities\SyncEvent $syncEvent
	 * @return void
	 * @throws ArgumentException
	 * @throws BaseException
	 */
	private function handleExportedInstances(Sync\Entities\SyncEvent $syncEvent): void
	{
		/** @var Sync\Entities\SyncEvent $instance */
		foreach ($syncEvent->getInstanceMap() as $instance)
		{
			if ($instance->getAction() !== Sync\Dictionary::SYNC_EVENT_ACTION['success'])
			{
				$this->handleExportedFailedSyncEvent($syncEvent);
				continue;
			}

			if ($instanceEventConnection = $instance->getEventConnection())
			{
				$instanceEventConnection
					->setEvent($instance->getEvent())
					->setConnection($syncEvent->getEventConnection()->getConnection())
				;

				if ($instanceEventConnection->getId())
				{
					$this->eventConnectionMapper->update($instanceEventConnection);
				}
				else
				{
					$this->eventConnectionMapper->create($instanceEventConnection);
				}
			}
		}
	}

	/**
	 * @param SyncEvent $syncEvent
	 * @param SyncEvent|null $existsSyncEvent
	 *
	 * @return bool
	 *
	 * @throws BaseException
	 * @throws LoaderException
	 * @throws ObjectException
	 */
	public function validateSyncEventChange(
		Sync\Entities\SyncEvent $syncEvent,
		Sync\Entities\SyncEvent $existsSyncEvent = null
	): bool
	{
		if (!$existsSyncEvent)
		{
			return true;
		}

		if (
			$syncEvent->getEventConnection() !== null
			&& (
				($syncEvent->getEventConnection()->getVendorVersionId() === $existsSyncEvent->getEventConnection()->getVendorVersionId())
				|| ($syncEvent->getEventConnection()->getEntityTag() === $existsSyncEvent->getEventConnection()->getEntityTag())
			)
		)
		{
			if (
				(
					$syncEvent->getEventConnection()->getConnection()->getVendor()->getCode()
					!== Sync\Google\Helper::GOOGLE_ACCOUNT_TYPE_API
				)
				|| !$this->hasDifferentEventFields($syncEvent, $existsSyncEvent))
			{
				return false;
			}
		}

		// Changing an event for an invitee
		// TODO: move it check to core.
		if ($existsSyncEvent->getEventId() !== $existsSyncEvent->getParentId())
		{
			// temporary this functionality is turned off
			// if (!$existsSyncEvent->getEvent()->isDeleted() && $this->hasDifferentEventFields($syncEvent, $existsSyncEvent))
			// {
			// 	$this->rollbackEvent(
			// 		$existsSyncEvent,
			// 		$syncEvent,
			// 		'CALENDAR_IMPORT_BLOCK_FROM_ATTENDEE'
			// 	);
			// }

			return false;
		}

		// Prevent changing events with booking
		if ($existsSyncEvent->getEvent()->getSpecialLabel() === ResourceBooking::EVENT_LABEL)
		{
			// temporary this functionality is turned off
			// if ($this->hasDifferentEventFields($syncEvent, $existsSyncEvent))
			// {
			//
				// $this->rollbackEvent(
				// 	$existsSyncEvent,
				// 	$syncEvent,
				// 	'CALENDAR_IMPORT_BLOCK_RESOURCE_BOOKING'
				// );
			// }

			return false;
		}

		// temporary this functionality is turned off

		// if ($syncEvent->getAction() !== Sync\Dictionary::SYNC_EVENT_ACTION['delete']
		// 	&& !$this->checkAttendeesAccessibility($existsSyncEvent->getEvent(), $syncEvent->getEvent()))
		// {
		// 	if ($this->hasDifferentEventFields($syncEvent, $existsSyncEvent))
		// 	{
		// 		$this->rollbackEvent(
		// 			$existsSyncEvent,
		// 			$syncEvent,
		// 			'CALENDAR_IMPORT_BLOCK_ATTENDEE_ACCESSIBILITY'
		// 		);
		// 	}
		//
		// 	return false;
		// }

		if ($syncEvent->isSingle() && $syncEvent->getEntityTag() !== $existsSyncEvent->getEntityTag())
		{
			return true;
		}

		if ($syncEvent->isRecurrence())
		{
			if ($syncEvent->getEntityTag() !== $existsSyncEvent->getEntityTag())
			{
				return true;
			}

			if ($syncEvent->hasInstances())
			{
				/** @var Sync\Entities\SyncEvent $instance */
				foreach ($syncEvent->getInstanceMap() as $key => $instance)
				{
					$existsInstanceMap = $existsSyncEvent->getInstanceMap();

					if (!$existsInstanceMap)
					{
						return true;
					}

					/** @var Sync\Entities\SyncEvent $existsInstance */
					$existsInstance = $existsInstanceMap->getItem($key);

					if (!$existsInstance)
					{
						return true;
					}

					if ($existsInstance->getEntityTag() !== $instance->getEntityTag())
					{
						return true;
					}
				}
			}
		}

		if ($syncEvent->isInstance())
		{
			return true;
		}

		return false;
	}

	private function hasDifferentEventFields(
		Sync\Entities\SyncEvent $syncEvent,
		Sync\Entities\SyncEvent $existSyncEvent
	): bool
	{
		if (!$syncEvent->getEvent() && !$existSyncEvent->getEvent())
		{
			return false;
		}

		if ($syncEvent->getAction() === Sync\Dictionary::SYNC_EVENT_ACTION['delete']
			&& !$existSyncEvent->getEvent()->isDeleted()
		)
		{
			return true;
		}

		$comparator = new EventCompareManager($syncEvent->getEvent(), $existSyncEvent->getEvent());

		$diff = $comparator->getDiff();
		$significantFields = [
			EventCompareManager::COMPARE_FIELDS['name'] => true,
			EventCompareManager::COMPARE_FIELDS['start'] => true,
			EventCompareManager::COMPARE_FIELDS['end'] => true,
			EventCompareManager::COMPARE_FIELDS['recurringRule'] => true,
			EventCompareManager::COMPARE_FIELDS['description'] => true,
			'excludedDates' => true,
		];
		$significantDiff = array_intersect_key($diff, $significantFields);

		return !empty($significantDiff);
	}

	/**
	 * @param Sync\Entities\SyncEvent $masterSyncEvent
	 * @param Sync\Entities\SyncEvent $instance
	 * @return Event
	 */
	public function addExdateToMasterEvent(Sync\Entities\SyncEvent $masterSyncEvent, Sync\Entities\SyncEvent $instance): Event
	{
		$masterEvent = $masterSyncEvent->getEvent();
		$exdateCollection = $masterEvent->getExcludedDateCollection();
		if ($exdateCollection === null)
		{
			$exdateCollection = new Core\Event\Properties\ExcludedDatesCollection();
			$masterEvent->setExcludedDateCollection($exdateCollection);
		}

		if ($instance->getEvent()->getOriginalDateFrom() instanceof Core\Base\Date)
		{
			$exdateCollection->add(
				(clone $instance->getEvent()->getOriginalDateFrom())
					->setDateTimeFormat(Core\Event\Properties\ExcludedDatesCollection::EXCLUDED_DATE_FORMAT)
			);
		}

		return $masterEvent;
	}

	/**
	 * @param SyncEvent $existsExternalSyncEvent
	 * @param SyncEvent $syncEvent
	 * @param string $messageCode
	 *
	 * @return void
	 *
	 * @throws BaseException
	 * @throws LoaderException
	 */
	private function rollbackEvent(
		Sync\Entities\SyncEvent $existsExternalSyncEvent,
		Sync\Entities\SyncEvent $syncEvent,
		string $messageCode
	): void
	{
		$muteNotice = $this->isNoticesMuted();
		if ($existsExternalSyncEvent->getEvent()->isDeleted())
		{
			$muteNotice = true;
			$syncStatus = Sync\Dictionary::SYNC_EVENT_ACTION['delete'];
		}
		else
		{
			$syncStatus = ($existsExternalSyncEvent->getEvent()->isRecurrence() || $syncEvent->getEvent()->isRecurrence())
				? Sync\Dictionary::SYNC_EVENT_ACTION['recreate']
				: Sync\Dictionary::SYNC_EVENT_ACTION['update']
			;
		}
		$existsExternalSyncEvent->getEventConnection()
			->setLastSyncStatus($syncStatus)
			->setVersion($existsExternalSyncEvent->getEvent()->getVersion() - 1);
		$this->eventConnectionMapper->update($existsExternalSyncEvent->getEventConnection());

		if (!$muteNotice)
		{
			$this->noticeUser($existsExternalSyncEvent, $messageCode);
		}
	}

	/**
	 * @return bool
	 */
	private function isNoticesMuted(): bool
	{
		return FlagRegistry::getInstance()->isFlag(Sync\Dictionary::FIRST_SYNC_FLAG_NAME);
	}

	/**
	 * @return Sync\Handlers\MasterPushHandler
	 */
	private function createPusher(): Sync\Handlers\MasterPushHandler
	{
		return new Sync\Handlers\MasterPushHandler(
			$this->factory->getConnection()->getOwner(),
			'google',
			$this->factory->getConnection()->getName()
		);
	}

	/**
	 * @param Sync\Entities\SyncSectionMap $externalSyncSectionMap
	 * @return void
	 */
	private function mergeSyncedSyncSectionsWithSavedSections(Sync\Entities\SyncSectionMap $externalSyncSectionMap): void
	{
		/** @var Sync\Entities\SyncSection $externalSyncSection */
		foreach ($externalSyncSectionMap as $key => $externalSyncSection)
		{
			if ($externalSyncSection->getSectionConnection() === null)
			{
				$externalSyncSectionMap->remove($key);

				continue;
			}

			/** @var Sync\Entities\SyncSection $savedSyncSection */
			if ($savedSyncSection = $this->syncSectionMap->has(
				$externalSyncSection->getSectionConnection()->getVendorSectionId()
			))
			{
				$savedSyncSection = $this->syncSectionMap->getItem(
					$externalSyncSection->getSectionConnection()->getVendorSectionId()
				);
				$externalSyncSection
					->getSectionConnection()
						->setId($savedSyncSection->getSectionConnection()->getId())
						->setSyncToken($savedSyncSection->getSectionConnection()->getSyncToken())
						->setPageToken($savedSyncSection->getSectionConnection()->getPageToken())
				;

				$savedSection = $savedSyncSection->getSection();
				$externalSyncSection
					->getSection()
						->setId($savedSection->getId())
						->setXmlId($savedSection->getXmlId())
						->setCreator($savedSection->getCreator())
						->setExternalType($savedSection->getExternalType())
						->setIsActive($savedSection->isActive())
						->setType($savedSection->getType())
				;
				if (empty($externalSyncSection->getSection()->getColor()))
				{
					$externalSyncSection->getSection()->setColor(
						$savedSection->getColor()
							?: Core\Property\ColorHelper::getOurColorRandom()
					);
				}
				if ($savedSection->isLocal())
				{
					$externalSyncSection->getSection()->setName($savedSection->getName());
				}
			}
		}
	}

	/**
	 * @throws BaseException
	 * @throws ArgumentException
	 */
	private function handleSectionsToLocalStorage(Sync\Entities\SyncSectionMap $syncSectionMap): void
	{
		/** @var Sync\Entities\SyncSection $syncSection */
		foreach ($syncSectionMap as $key => $syncSection)
		{
			if (!$this->validateSyncSectionBeforeSave($syncSection))
			{
				$this->updateFailedSyncSection($syncSection);

				continue;
			}

			if ($syncSection->getAction() === Sync\Dictionary::SYNC_STATUS['delete'])
			{
				if ($syncSection->getSection()->isLocal())
				{
					$this->createLocalDeletedSection($syncSection);
				}
				else
				{
					$this->deleteSyncSectionFromLocalStorage($syncSection);
					continue;
				}
			}

			if ($syncSection->getSection()->isNew())
			{
				/** @var Core\Section\Section $section */
				// TODO: change later to saveManager
				$this->sectionMapper->create($syncSection->getSection());
				$this->savePermissions($syncSection);

				$this->sectionConnectionMapper->create(
					$syncSection->getSectionConnection()->setSection($syncSection->getSection())
				);
			}
			else
			{
				$this->sectionMapper->update($syncSection->getSection());
				$sectionConnection = $syncSection->getSectionConnection();
				$sectionConnection->setSection($syncSection->getSection());

				$sectionConnection->isNew()
					? $this->sectionConnectionMapper->create($sectionConnection)
					: $this->sectionConnectionMapper->update($sectionConnection)
				;
			}

			$syncSection->setAction(Sync\Dictionary::SYNC_SECTION_ACTION['success']);

			$this->syncSectionMap->add($syncSection, $key);
		}
	}

	/**
	 * @param Sync\Entities\SyncSectionMap $externalSectionMap
	 * @return void
	 */
	private function removeDeletedExternalSections(Sync\Entities\SyncSectionMap $externalSectionMap): void
	{
		if (!$this->isFullSync)
		{
			return;
		}

		$deleteCandidates = (new Sync\Entities\SyncSectionMap(
			array_diff_key($this->syncSectionMap->getCollection(), $externalSectionMap->getCollection()),
		))->getNonLocalSections();

		/** @var Sync\Entities\SyncSection $syncSection */
		foreach ($deleteCandidates as $syncSection)
		{
			if (
				($syncSection->getSection() === null)
				|| ($syncSection->getSectionConnection() === null)
			)
			{
				continue;
			}
			$this->deleteSyncSectionFromLocalStorage($syncSection);
		}
	}

	private function createLocalDeletedSection(Sync\Entities\SyncSection $syncSection)
	{

	}

	/**
	 * @param Sync\Entities\SyncSection $syncSection
	 * @return void
	 */
	private function deleteSyncSectionFromLocalStorage(Sync\Entities\SyncSection $syncSection): void
	{
		if ($syncSection->getSectionConnection()->getId() !== null)
		{
			$this->sectionConnectionMapper->delete($syncSection->getSectionConnection());
			$this->syncSectionMap->remove($syncSection->getSectionConnection()->getVendorSectionId());
		}

		if ($syncSection->getSection()->getId() !== null)
		{
			$this->sectionMapper->delete($syncSection->getSection(), ['softDelete' => false]);
			$this->syncSectionMap->remove($syncSection->getSection()->getId());
		}
	}

	/**
	 * @param Sync\Entities\SyncSectionMap $externalSectionMap
	 * @return Sync\Entities\SyncSectionMap
	 */
	private function getFilteredSectionMapForExport(
		Sync\Entities\SyncSectionMap $externalSectionMap
	): Sync\Entities\SyncSectionMap
	{
		return (new Sync\Entities\SyncSectionMap(
			array_diff_key(
				$this->isFullSync
					? $this->syncSectionMap->getCollection()
					: $this->syncSectionMap
						->getItemsByKeys(array_keys($externalSectionMap->getCollection()))
						->getCollection(),
				$externalSectionMap->getCollection()
			),
		))->getLocalSections();
	}

	/**
	 * @param Sync\Entities\SyncSectionMap $syncSectionMap
	 * @return Sync\Entities\SyncSectionMap
	 */
	public function getSyncSectionMapBySyncSectionMap(
		Sync\Entities\SyncSectionMap $syncSectionMap
	): Sync\Entities\SyncSectionMap
	{
		$syncSectionList = $this->isFullSync
			? $this->syncSectionMap->getCollection()
			: $this->syncSectionMap
				->getItemsByKeys(array_keys($syncSectionMap->getCollection()))
				->getCollection();

		return new Sync\Entities\SyncSectionMap(
			array_diff_key($syncSectionList, $syncSectionMap->getCollection()),
		);
	}

	/**
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getLocalEventsForExport(): ?Sync\Entities\SyncEventMap
	{
		$sectionIdCollection = [];
		/** @var Sync\Entities\SyncSection $syncSection */
		foreach ($this->syncSectionMap as $syncSection)
		{
			$sectionIdCollection[] = $syncSection->getSection()->getId();
		}

		// foreach ($this->importedLocalEventUidList as $eventUid)
		// {
		// 	$candidatesForExport->remove($eventUid);
		// }

		return $this->syncEventFactory->getSyncEventMapBySyncSectionIdCollectionForExport(
			$sectionIdCollection,
			$this->factory->getConnection()->getOwner()->getId(),
			$this->factory->getConnection()->getId()
		)->getNotSuccessSyncEvent();
	}

	/**
	 * @param Sync\Entities\SyncEventMap $externalSyncEventMap
	 *
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws LoaderException
	 * @throws SystemException
	 */
	private function handleEventsToLocalStorage(Sync\Entities\SyncEventMap $externalSyncEventMap): void
	{
		/** @var Sync\Entities\SyncEvent $syncEvent */
		foreach ($externalSyncEventMap as $key => $syncEvent)
		{
			/** @var Sync\Entities\SyncEvent $existsExternalSyncEvent */
			$existsExternalSyncEvent = $this->syncEventMap->getItem($key);

			// TODO: implement logic of saving attendees events
			if (!$this->validateSyncEventChange($syncEvent, $existsExternalSyncEvent))
			{
				continue;
			}

			$masterSyncEvent = null;
			if (
				($syncEvent->isInstance() || $syncEvent->getVendorRecurrenceId())
				&& $masterSyncEvent = $this->getMasterSyncEvent($syncEvent)
			)
			{
				if ($masterSyncEvent->getId() !== $masterSyncEvent->getParentId())
				{
					continue;
				}
			}

			$this->handleSyncEvent($syncEvent, $syncEvent->getVendorEventId(), $masterSyncEvent);

			if (
				$existsExternalSyncEvent
				&& (
					$syncEvent->isRecurrence()
					|| ($syncEvent->getAction() === Sync\Dictionary::SYNC_EVENT_ACTION['delete'])
				)
			)
			{
				$this->removeDeprecatedInstances($existsExternalSyncEvent, $syncEvent);
			}

			if ($syncEvent->hasInstances())
			{
				$collection = $syncEvent->getInstanceMap()->getCollection();
				array_walk($collection, [$this, 'handleSyncEvent'], $syncEvent);
			}

			$this->syncEventMap->updateItem($syncEvent, $syncEvent->getVendorEventId());
		}
	}

	/**
	 * @param Sync\Entities\SyncEvent $syncEvent
	 *
	 * @return Sync\Entities\SyncEvent
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws SystemException
	 */
	private function getMasterSyncEvent(Sync\Entities\SyncEvent $syncEvent): ?Sync\Entities\SyncEvent
	{
		$eventConnection = $syncEvent->getEventConnection();
		if ($eventConnection === null)
		{
			throw new BaseException('you should set EventConnection in SyncEvent');
		}

		if ($masterSyncEvent = $this->syncEventMap->getItem($eventConnection->getRecurrenceId()))
		{
			return $masterSyncEvent;
		}

		return $this->syncEventFactory->getSyncEventCollectionByVendorIdList(
			[$eventConnection->getRecurrenceId()],
			$this->factory->getConnection()->getId()
		)->fetch();
	}

	/**
	 * @param Sync\Entities\SyncEvent $masterSyncEvent
	 * @param Core\Base\Date $excludedDate
	 * @return void
	 */
	private function prepareExcludedDatesMasterEvent(Sync\Entities\SyncEvent $masterSyncEvent, Core\Base\Date $excludedDate): void
	{
		$masterEvent = $masterSyncEvent->getEvent();

		$date = clone $excludedDate;
		$date->format(Core\Event\Properties\ExcludedDatesCollection::EXCLUDED_DATE_FORMAT);

		if ($masterEvent->getExcludedDateCollection())
		{
			$masterEvent->getExcludedDateCollection()->add($date);
		}
		else
		{
			$masterEvent->setExcludedDateCollection(new Core\Event\Properties\ExcludedDatesCollection([$date]));
		}
	}

	/**
	 * @param Event $event
	 * @param Sync\Entities\SyncSection $syncSection
	 *
	 * @return void
	 *
	 * @throws BaseException
	 */
	private function prepareNewEvent(Event $event, Sync\Entities\SyncSection $syncSection): void
	{
		$section = $syncSection->getSection();
		$owner = $section->getOwner();
		if ($owner === null)
		{
			throw new BaseException('section has not owner');
		}

		$event
			->setSection($syncSection->getSection())
			->setOwner($owner)
			->setCreator($owner)
			->setEventHost($owner)
			->setMeetingDescription($this->getMeetingDescriptionForNewEvent($owner))
			->setIsActive(true)
			->setIsDeleted(false)
			->setCalendarType($owner->getType())
		;
	}

	/**
	 * @param Sync\Entities\SyncEventMap $localEventCollection
	 * @param string $vendorId
	 * @param SyncEventMergeHandler $handlerMerge
	 * @param Sync\Entities\SyncEvent $syncEvent
	 * @param Sync\Connection\EventConnection|null $eventConnection
	 * @param Core\Event\Event $event
	 * @param Sync\Entities\SyncSection $syncSection
	 * @return Core\Event\Event
	 * @throws Core\Base\BaseException
	 */
	public function handleMerge(
		Sync\Entities\SyncEventMap $localEventCollection,
		string $vendorId,
		SyncEventMergeHandler $handlerMerge,
		Sync\Entities\SyncEvent $syncEvent,
		?Sync\Connection\EventConnection $eventConnection,
		Core\Event\Event $event,
		Sync\Entities\SyncSection $syncSection
	): Core\Event\Event
	{
		$mergedSyncEvent = null;
		if ($localEventCollection->has($vendorId))
		{
			$mergedSyncEvent = $handlerMerge(
				$localEventCollection->getItem($vendorId),
				$syncEvent,
				$localEventCollection->getItem($vendorId)->getEvent()->getId()
			);
			$eventConnection->setId($localEventCollection->getItem($vendorId)->getEventConnection()->getId());
		}
		elseif (
			$syncEvent->getEventConnection()
			&& $localEventCollection->has($syncEvent->getEventConnection()
				->getRecurrenceId())
		)
		{
			/** @var Sync\Entities\SyncEvent $masterSyncEvent */
			$masterSyncEvent = $localEventCollection->getItem($syncEvent->getEventConnection()->getRecurrenceId());
			// merge with master event
			$mergedSyncEvent = $handlerMerge(
				$masterSyncEvent,
				$syncEvent
			);
			$mergedSyncEvent->getEvent()->setRecurrenceId($masterSyncEvent->getEvent()->getId())
			;
		}
		if ($mergedSyncEvent !== null)
		{
			$event = $mergedSyncEvent->getEvent();
		}
		else
		{
			$this->prepareNewEvent($event, $syncSection);
		}
		return $event;
	}

	/**
	 * @param Sync\Entities\SyncEvent $syncEvent
	 * @return void
	 * @throws BaseException
	 */
	private function mergeExternalEventWithLocalParams(Sync\Entities\SyncEvent $syncEvent): void
	{
		/** @var Sync\Entities\SyncEvent $existsSyncEvent */
		if ($existsSyncEvent = $this->syncEventMap->getItem($syncEvent->getEventConnection()->getVendorEventId()))
		{
			$this->mergeSyncEvent(
				$existsSyncEvent,
				$syncEvent,
				$existsSyncEvent->getEventId(),
				$existsSyncEvent->getEventConnection()->getId()
			);

			if (
				$syncEvent->isRecurrence()
				&& $syncEvent->hasInstances()
			)
			{
				foreach ($syncEvent->getInstanceMap() as $instanceSyncEvent)
				{
					if (
						$existsSyncEvent->hasInstances()
						&& $instanceSyncEvent->getEvent()->getOriginalDateFrom()
						&& $existsInstanceSyncEvent = $existsSyncEvent->getInstanceMap()->getItem(
							Sync\Entities\InstanceMap::getKeyByDate(
								$instanceSyncEvent->getEvent()->getOriginalDateFrom()
							)
						)
					)
					{
						$this->mergeSyncEvent(
							$existsInstanceSyncEvent,
							$instanceSyncEvent,
							$existsInstanceSyncEvent->getEventId(),
							$existsInstanceSyncEvent->getEventConnection()->getId()
						);
					}
					else
					{
						$this->prepareNewSyncEvent($syncEvent);
					}
				}
			}

			return;
		}

		/** @var Sync\Entities\SyncEvent $existsMasterSyncEvent */
		if ($existsMasterSyncEvent = $this->syncEventMap->getItem($syncEvent->getEventConnection()->getRecurrenceId()))
		{
			if (
				$existsMasterSyncEvent->hasInstances()
				&& $syncEvent->getEvent()->getOriginalDateFrom()
				&& $existsInstanceSyncEvent = $existsMasterSyncEvent->getInstanceMap()->getItem(
					Sync\Entities\InstanceMap::getKeyByDate(
						$syncEvent->getEvent()->getOriginalDateFrom()
					)
				)
			)
			{
				$this->mergeSyncEvent(
					$existsInstanceSyncEvent,
					$syncEvent,
					$existsInstanceSyncEvent->getEventId(),
					$existsInstanceSyncEvent->getEventConnection()->getId()
				);

				return;
			}

			$this->mergeSyncEvent($existsMasterSyncEvent, $syncEvent);

			return;
		}

		if ($syncEvent->getAction() !== Sync\Dictionary::SYNC_EVENT_ACTION['delete'])
		{
			$this->prepareNewSyncEvent($syncEvent);
		}
	}

	/**
	 * @param SyncEvent $existsSyncEvent
	 * @param SyncEvent $externalSyncEvent
	 * @param int|null $id
	 * @param int|null $eventConnectionId
	 * @return void
	 * @throws BaseException
	 */
	private function mergeSyncEvent(
		Sync\Entities\SyncEvent $existsSyncEvent,
		Sync\Entities\SyncEvent $externalSyncEvent,
		int $id = null,
		int $eventConnectionId = null
	): void
	{
		$handler = new SyncEventMergeHandler();
		$handler($existsSyncEvent, $externalSyncEvent, $id, $eventConnectionId);
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	private function prepareLocalSyncEventMapWithVendorEventId(Sync\Entities\SyncEventMap $externalSyncEventMap): void
	{
		$vendorEventIdList = [];
		/** @var Sync\Entities\SyncEvent $item */
		foreach ($externalSyncEventMap->getCollection() as $item)
		{
			$vendorEventIdList[] = $item->getVendorEventId();
			if ($item->isInstance())
			{
				$vendorEventIdList[] = $item->getVendorRecurrenceId();
			}
			$this->importedLocalEventUidList[] = $item->getUid();
		}

		$this->syncEventMap = $this->syncEventFactory->getSyncEventCollectionByVendorIdList(
			array_unique($vendorEventIdList),
			$this->factory->getConnection()->getId()
		);
	}

	/**
	 * @param Sync\Entities\SyncEvent $syncEvent
	 * @return void
	 */
	private function prepareNewSyncEvent(Sync\Entities\SyncEvent $syncEvent): void
	{

	}

	/**
	 * @throws BaseException
	 * @throws ArgumentException
	 */
	private function updateExportedEvents(Sync\Entities\SyncEventMap $localEventMap): void
	{
		/** @var Sync\Entities\SyncEvent $syncEvent */
		foreach ($localEventMap as $syncEvent)
		{
			if ($syncEvent->getAction() !== Sync\Dictionary::SYNC_EVENT_ACTION['success'])
			{
				$this->handleExportedFailedSyncEvent($syncEvent);
				continue;
			}

			if ($eventConnection = $syncEvent->getEventConnection())
			{
				if ($eventConnection->getId())
				{
					$this->eventConnectionMapper->update($eventConnection);
				}
				else
				{
					$this->eventConnectionMapper->create($eventConnection);
				}

				if ($syncEvent->hasInstances())
				{
					$this->handleExportedInstances($syncEvent);
				}
			}
		}
	}

	/**
	 * @param Event $masterEvent
	 * @return void
	 */
	private function updateMasterExdate(Event $masterEvent): void
	{
		$handler = new UpdateMasterExdateHandler();
		$handler($masterEvent);
	}

	/**
	 * @param Sync\Entities\SyncSection $syncSection
	 * @return bool
	 */
	private function validateSyncSectionBeforeSave(Sync\Entities\SyncSection $syncSection): bool
	{
		return $syncSection->getSection() && $syncSection->getSectionConnection();
	}

	/**
	 * @param Sync\Entities\SyncSection $syncSection
	 * @return void
	 */
	private function updateFailedSyncSection(Sync\Entities\SyncSection $syncSection): void
	{

	}

	/**
	 * @return void
	 */
	private function filterBrokenSyncSections(): void
	{
		/**
		 * @var string $key
		 * @var Sync\Entities\SyncSection $item
		 */
		foreach ($this->syncSectionMap as $key => $item)
		{
			if ($item->getSectionConnection() === null)
			{
				$this->syncSectionMap->remove($key);
			}
		}
	}

	/**
	 * @return void
	 */
	private function handleDeleteRecurrenceEvent(SyncEvent $syncEvent)
	{

	}

	/**
	 * @return void
	 */
	private function handleDeleteSingleEvent(SyncEvent $syncEvent)
	{

	}

	/**
	 * @param SyncEvent $syncEvent
	 *
	 * @return void
	 *
	 * @throws BaseException
	 */
	private function handleExportedFailedSyncEvent(Sync\Entities\SyncEvent $syncEvent): void
	{
		if (
			($syncEvent->getEventConnection() === null)
			|| ($syncEvent->getEventConnection()->getId() === null)
		)
		{
			return;
		}

		$eventConnection = $syncEvent->getEventConnection();

		switch ($syncEvent->getAction())
		{
			case Sync\Dictionary::SYNC_EVENT_ACTION['delete']:
				$eventConnection->setLastSyncStatus(Sync\Dictionary::SYNC_STATUS['delete']);
				break;
			default:
				$eventConnection->setLastSyncStatus(Sync\Dictionary::SYNC_STATUS['update']);
				break;
		}

		$this->eventConnectionMapper->update($eventConnection);
	}

	/**
	 * @param Sync\Entities\SyncSectionMap $syncSectionMap
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function blockSectionPush(Sync\Entities\SyncSectionMap $syncSectionMap): void
	{
		$pushManager = new PushManager();
		/** @var Sync\Entities\SyncSection $syncSection */
		foreach ($syncSectionMap as $syncSection)
		{
			$pushManager->setBlockPush(
				$pushManager->getPush(PushManager::TYPE_SECTION, $syncSection->getSection()->getId())
			);

			if (
				($syncSection->getSectionConnection() !== null)
				&& ($syncSection->getSectionConnection()->getId() !== null)
			)
			{
				$pushManager->setBlockPush(
					$pushManager->getPush(
						PushManager::TYPE_SECTION_CONNECTION,
						$syncSection->getSectionConnection()->getId()
					)
				);
			}
		}
	}

	/**
	 * @param Sync\Entities\SyncSectionMap $syncSectionMap
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function unblockSectionPush(Sync\Entities\SyncSectionMap $syncSectionMap): void
	{
		$pushManager = new PushManager();
		/** @var Sync\Entities\SyncSection $syncSection */
		foreach ($syncSectionMap as $syncSection)
		{
			$pushManager->setUnblockPush(
				$pushManager->getPush(PushManager::TYPE_SECTION, $syncSection->getSection()->getId())
			);

			if ($syncSection->getSectionConnection() !== null
				&& $syncSection->getSectionConnection()->getId() !== null
			)
			{
				$pushManager->setUnblockPush(
					$pushManager->getPush(
						PushManager::TYPE_SECTION_CONNECTION,
						$syncSection->getSectionConnection()->getId()
					)
				);
			}
		}
	}

	/**
	 * @param Sync\Entities\SyncEvent $existsExternalSyncEvent
	 * @param Sync\Entities\SyncEvent $syncEvent
	 *
	 * @return void
	 */
	private function removeDeprecatedInstances(
		Sync\Entities\SyncEvent $existsExternalSyncEvent,
		Sync\Entities\SyncEvent $syncEvent
	)
	{
		if ($existsExternalSyncEvent->hasInstances())
		{
			/** @var Sync\Entities\SyncEvent $oldInstance */
			foreach ($existsExternalSyncEvent->getInstanceMap() as $key => $oldInstance)
			{
				if (!$syncEvent->hasInstances() || empty($syncEvent->getInstanceMap()->getItem($key)))
				{
					$this->eventConnectionMapper->delete($oldInstance->getEventConnection(), ['softDelete' => false]);
					$this->eventMapper->delete($oldInstance->getEvent(), ['softDelete' => false]);
				}
			}
		}
	}

	/**
	 * @param Sync\Entities\SyncSectionMap $syncSectionMap
	 * @return Sync\Entities\SyncSectionMap
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function prepareSyncSectionBeforeExport(Sync\Entities\SyncSectionMap $syncSectionMap): Sync\Entities\SyncSectionMap
	{
		$syncSectionKeyIdList = [];
		/** @var Sync\Entities\SyncSection $syncSection */
		foreach ($syncSectionMap as $key => $syncSection)
		{
			if ($syncSection->getSectionConnection() === null)
			{
				continue;
			}

			if (in_array($syncSection->getSection()->getId(), $syncSectionKeyIdList, true))
			{
				$syncSectionMap->remove($key);
			}
			else
			{
				$syncSectionKeyIdList[$syncSection->getSectionConnection()->getVendorSectionId()] = $syncSection->getSection()->getId();
			}

			$this->sectionConnectionMapper->delete($syncSection->getSectionConnection(), ['softDelete' => false]);
			$this->syncSectionMap->remove($key);
			$syncSection->setSectionConnection(null);
		}

		// todo optimize this process after update mappers
		if ($syncSectionKeyIdList)
		{
			$syncEventMap = $this->syncEventFactory->getSyncEventMapBySyncSectionIdCollectionForExport(
				$syncSectionKeyIdList,
				$this->factory->getConnection()->getOwner()->getId(),
				$this->factory->getConnection()->getId()
			);
			if ($syncEventMap->count())
			{
				/** @var Sync\Entities\SyncEvent $syncEvent */
				foreach ($syncEventMap as $syncEvent)
				{
					if ($syncEvent->getEventConnection() === null)
					{
						continue;
					}

					$this->eventConnectionMapper->delete($syncEvent->getEventConnection(), ['softDelete' => false]);
				}
			}
		}

		return $syncSectionMap;
	}

	/**
	 * @param Sync\Entities\SyncEvent $syncEvent
	 * @param string $messageCode
	 *
	 * @return void
	 *
	 * @throws LoaderException
	 */
	private function noticeUser(Sync\Entities\SyncEvent $syncEvent, string $messageCode = '')
	{
		if (Loader::includeModule('im') && Loader::includeModule('pull'))
		{
			$path = CCalendar::GetPath(
				$syncEvent->getEvent()->getOwner()->getType(),
				$syncEvent->getEvent()->getOwner()->getId(),
				true);
			$uri = (new Uri($path))
				->deleteParams(["action", "sessid", "bx_event_calendar_request", "EVENT_ID", "EVENT_DATE"])
				->addParams([
					'EVENT_ID' => $syncEvent->getEvent()->getId()])
			;

			NotificationManager::sendBlockChangeNotification(
				$syncEvent->getEventConnection()->getConnection()->getOwner()->getId(),
				$messageCode,
				[
					'#EVENT_URL#' => $uri->getUri(),
					'#EVENT_TITLE#' => $syncEvent->getEvent()->getName(),
					'EVENT_ID' => $syncEvent->getEvent()->getId(),
				]
			);
		}
	}

	/**
	 * @param Event $baseEvent
	 * @param Event $importedEvent
	 *
	 * @return bool
	 *
	 * @throws ObjectException
	 */
	private function checkAttendeesAccessibility(
		Event $baseEvent,
		Event $importedEvent
	): bool
	{
		if (
			$importedEvent->getStart()->format('c') === $baseEvent->getStart()->format('c')
			&& $importedEvent->getEnd()->format('c') === $baseEvent->getEnd()->format('c')
		)
		{
			return true;
		}

		$codes = $baseEvent->getAttendeesCollection()->getAttendeesCodes();
		if (count($codes) > 1)
		{
			$userIds = CCalendar::GetDestinationUsers($codes);
			if ($userIds = array_filter($userIds))
			{
				$localTime = new \DateTime();
				$start = clone $importedEvent->getStart();
				$end = clone $importedEvent->getEnd();
				$accessibility = CCalendar::GetAccessibilityForUsers([
					'users' => $userIds,
					'from' => $start->setTimezone($localTime->getTimezone())->setTime(0,0,0)->toString(),
					'to' => $end->setTimezone($localTime->getTimezone())->setTime(23,59,59)->toString(),
					'curEventId' => $baseEvent->getId(),
					'checkPermissions' => false,
				]);

				foreach ($accessibility as $events)
				{
					foreach ($events as $eventData)
					{
						$eventFrom  = new Date(new DateTime($eventData['DATE_FROM']));
						$eventTo  = new Date(new DateTime($eventData['DATE_TO']));
						if ($eventFrom >= $importedEvent->getEnd() || $eventTo <=$importedEvent->getStart())
						{
							continue;
						}
						if ($eventData['ACCESSIBILITY'] === 'busy')
						{
							return false;
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * @param Connection $connection
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function renewSubscription(Connection $connection)
	{
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$links = $mapperFactory->getSectionConnection()->getMap([
			'=CONNECTION_ID' => $connection->getId(),
			'=ACTIVE' => 'Y'
		]);

		$manager = $this->getOutgoingManager($connection);
		foreach ($links as $link)
		{
			$manager->subscribeSection($link);
		}

		$manager->subscribeConnection();
	}

	/**
	 * @param Connection $connection
	 *
	 * @return OutgoingManager|mixed
	 *
	 * @throws ObjectNotFoundException
	 */
	private function getOutgoingManager(Connection $connection)
	{
		if (empty(static::$outgoingManagersCache[$connection->getId()]))
		{
			static::$outgoingManagersCache[$connection->getId()] = new OutgoingManager($connection);
		}

		return static::$outgoingManagersCache[$connection->getId()];
	}
}
