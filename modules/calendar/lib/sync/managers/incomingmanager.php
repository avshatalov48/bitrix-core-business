<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Factories\EventConnectionFactory;
use Bitrix\Calendar\Sync\Factories\FactoryBuilder;
use Bitrix\Calendar\Sync\Factories\FactoryInterface;
use Bitrix\Calendar\Sync\Factories\SectionConnectionFactory;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Main\Type\DateTime;
use CCalendar;
use Bitrix\Calendar\Core;
use CCalendarEvent;
use CCalendarSect;
use Exception;

/**
 * Class IncomingManager
 */
class IncomingManager
{
	private Connection $connection;

	private FactoryInterface $factory;

	private VendorSynchronization $syncManager;
	/** @var Core\Mappers\Factory  */
	private Core\Mappers\Factory $mapperFactory;

	/**
	 * @param Connection $connection
	 *
	 * @throws ObjectNotFoundException
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;

		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	/**
	 * @return Result
	 *
	 * @throws Exception
	 */
	public function importSections(): Result
	{
		// In this case we can guess, they were deleted on the vendor side.
		$resultData = [
			'links' => [
				'updated' => [],
				'imported' => [],
				'skipped' => [],
			],
			'vendorSections' => [],
		];
		$result = new Result();

		$getResult = $this->getFactory()->getIncomingSectionManager()->getSections();
		if ($getResult->isSuccess())
		{
			$sections = $getResult->getData()['externalSyncSectionMap'];

			$mapper = $this->mapperFactory->getSection();
			/** @var SyncSection $syncSection */
			foreach ($sections as $syncSection)
			{
				$link = null;
				try {
					if ($link = $this->getSectionLinkByVendorId($syncSection->getSectionConnection()->getVendorSectionId()))
					{
						$resultData['linkedSectionIds'][] = $link->getSection()->getId();
						if (!$link->isActive())
						{
							$resultData['links']['skipped'][] = $link;
							continue;
						}
						if (
							!empty($syncSection->getAction() === 'updated')
							&& $syncSection->getSection()->getDateModified() > $link->getSection()->getDateModified()
						)
						{
							$section = $this->mergeSections($link->getSection(), $syncSection->getSection());
							$mapper->update($section, [
								'originalFrom' => $this->connection->getVendor()->getCode(),
							]);
							(new Core\Mappers\SectionConnection())->update($link);

							$resultData['importedSectionIds'][] = $link->getSection()->getId();
							$resultData['links']['updated'][] = $link;
						}
					}
					else
					{
						$link = $this->importSectionSimple(
							$syncSection->getSection(),
							$syncSection->getSectionConnection()->getVendorSectionId(),
							$syncSection->getSectionConnection()->getVersionId() ?? null,
							$syncSection->getSectionConnection()->isPrimary() ?? null,
						);
						$resultData['links']['imported'][] = $link;
					}
					if (!empty($link))
					{
						$resultData['importedSectionIds'][] = $link->getSection()->getId();
					}

				}
				catch (SystemException $e)
				{
					$resultData['sections'][$syncSection['id']] = Dictionary::SYNC_STATUS['failed'];
				}
			}

			return $result->setData($resultData);
		}
		else
		{
			$result->addErrors($getResult->getErrors());
			return $result;
		}
	}


	/**
	 * @param string $sectionId
	 *
	 * @return SectionConnection|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getSectionLinkByVendorId(string $sectionId): ?SectionConnection
	{
		$mapper = $this->mapperFactory->getSectionConnection();
		$map = $mapper->getMap([
				'=CONNECTION_ID' => $this->connection->getId(),
				'=VENDOR_SECTION_ID' => $sectionId,
			]);

		return $map->fetch();
	}

	/**
	 * @return Result
	 *
	 * @throws Exception
	 *
	 * @deprecated too smart method with high level of dependencies.
	 * call several methods to reach the same result
	 */
	public function import(): Result
	{
		$resultData = [];
		$result = new Result();
		/** @var Section[] $sections */
		$sections = $this->getFactory()->getSectionManager()->getSections($this->connection);

		/** @var $vendorSectionPack []<
			section => Section
		 	id => string Vendor ID
		    version => string Vendor version >
		 */
		foreach ($sections as $vendorSectionPack)
		{
			try {
				$sectionLink = $this->importSection(
					$vendorSectionPack['section'],
					$vendorSectionPack['id'],
					$vendorSectionPack['version'] ?? null,
					$vendorSectionPack['is_primary'] ?? null,

				);
				$resultData['importedSectionIds'][] = $sectionLink->getSection()->getId();
				if ($sectionLink->isActive())
				{
					$eventsResult = $this->importSectionEvents($sectionLink);
					$resultData['events'][$vendorSectionPack['id']] = $eventsResult->getData();
					$resultData['sections'][$vendorSectionPack['id']] = Dictionary::SYNC_STATUS['success'];
				}
				else
				{
					$resultData['sections'][$vendorSectionPack['id']] = Dictionary::SYNC_STATUS['inactive'];
					$resultData['events'][$vendorSectionPack['id']] = null;
				}
			} catch (SystemException $e) {
				$resultData['sections'][$vendorSectionPack['id']] = Dictionary::SYNC_STATUS['failed'];
			}
		}

		return $result->setData($resultData);
	}

	/**
	 * @return FactoryInterface
	 */
	private function getFactory(): FactoryInterface
	{
		if (empty($this->factory))
		{
			$this->factory = FactoryBuilder::create(
				$this->connection->getVendor()->getCode(),
				$this->connection,
				new Context()
			);
		}

		return $this->factory;
	}

	/**
	 * @param Section $section
	 * @param string $vendorId
	 * @param string|null $vendorVersion
	 * @param bool $isPrimary
	 *
	 * @return SectionConnection
	 *
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 *
	 * @deprecated method has a lot of responsibilities.
	 * use method importSectionSimple() and subscribeSection()
	 */
	private function importSection(
		Section $section,
		string  $vendorId,
		string $vendorVersion = null,
		bool $isPrimary = false
	): SectionConnection
	{
		$sectionFactory = new SectionConnectionFactory();
		$link = $sectionFactory->getSectionConnection([
			'filter' => [
				'CONNECTION_ID' => $this->connection->getId(),
				'VENDOR_SECTION_ID' => $vendorId,
			],
			'connectionObject' => $this->connection,
		]);
		if (!$link)
		{
			$fields = [
				'NAME' => $section->getName(),
				'ACTIVE' => 'Y',
				'DESCRIPTION' => $section->getDescription() ?? '',
				'COLOR' => $section->getColor() ?? '',
				'CAL_TYPE' => $this->connection->getOwner()
					? $this->connection->getOwner()->getType()
					: Core\Role\User::TYPE
				,
				'OWNER_ID' => $this->connection->getOwner()
					? $this->connection->getOwner()->getId()
					: null
				,
				'CREATED_BY' => $this->connection->getOwner()->getId(),
				'DATE_CREATE' => new DateTime(),
				'TIMESTAMP_X' => new DateTime(),
				'EXTERNAL_TYPE' => $this->connection->getVendor()->getCode(),
			];
			if ($sectionId = CCalendar::SaveSection([
				'arFields' => $fields,
				'originalFrom' => $this->connection->getVendor()->getCode(),

			]))
			{
				$section = $this->mapperFactory->getSection()->resetCacheById($sectionId)->getById($sectionId);

				$protoLink = (new SectionConnection())
					->setSection($section)
					->setConnection($this->connection)
					->setVendorSectionId($vendorId)
					->setVersionId($vendorVersion)
					->setPrimary($isPrimary)
					;
				$link = $this->mapperFactory->getSectionConnection()->create($protoLink);
			}
			else
			{
				throw new SystemException("Can't create Bitrix Calendar Section", 500, __FILE__, __LINE__ );
			}
		}

		if ($link)
		{
			$this->subscribeSection($link);
		}

		return $link;
	}

	/**
	 * @param Section $section
	 * @param string $vendorId
	 * @param string|null $vendorVersion
	 * @param bool|null $isPrimary
	 *
	 * @return SectionConnection
	 *
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function importSectionSimple(
		Section $section,
		string  $vendorId,
		string $vendorVersion = null,
		bool $isPrimary = null
	): SectionConnection
	{

		if ($section = $this->saveSection($section))
		{
			if ($link = $this->createSectionConnection($section, $vendorId, $vendorVersion, $isPrimary))
			{
				return $link;
			}
			else
			{
				throw new SystemException("Can't create Section Connection link.", 500, __FILE__, __LINE__ );
			}
		}
		else
		{
			throw new SystemException("Can't create Bitrix Calendar Section.", 500, __FILE__, __LINE__ );
		}
	}

	/**
	 * @param Section $section
	 * @param string $vendorId
	 * @param string|null $vendorVersion
	 * @param bool|null $isPrimary
	 *
	 * @return SectionConnection|null
	 *
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 */
	private function createSectionConnection(
		Section $section,
		string $vendorId,
		string $vendorVersion = null,
		bool $isPrimary = null
	): ?SectionConnection
	{
		$entity = (new SectionConnection())
			->setSection($section)
			->setConnection($this->connection)
			->setVendorSectionId($vendorId)
			->setVersionId($vendorVersion)
			->setPrimary($isPrimary ?? false)
		;
		/** @var SectionConnection */
		$result = (new Core\Mappers\SectionConnection())->create($entity);

		return $result;
	}

	/**
	 * @param SectionConnection $link
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function subscribeSection(SectionConnection $link): Result
	{
		$mainResult = new Result();
		if ($this->getSyncManager()->canSubscribeSection())
		{
			$pushManager = new PushManager();
			$subscription = $pushManager->getPush(
				PushManager::TYPE_SECTION_CONNECTION,
				$link->getId()
			);
			if ($subscription && !$subscription->isExpired())
			{
				$result = $this->getSyncManager()->renewPush($subscription);
				if ($result->isSuccess())
				{
					$mainResult = $pushManager->renewPush($subscription, $result->getData());
				}
				else
				{
					$mainResult->addError(new Error('Error of renew subscription.'));
					$mainResult->addErrors($result->getErrors());
				}
			}
			else
			{
				$subscribeResult = $this->getSyncManager()->subscribeSection($link);
				if ($subscribeResult->isSuccess())
				{
					if ($subscription !== null)
					{
						$pushManager->renewPush($subscription, $subscribeResult->getData());
					}
					else
					{
						$pushManager->addPush(
							'SECTION_CONNECTION',
							$link->getId(),
							$subscribeResult->getData()
						);
					}
				}
				else
				{
					$mainResult->addError(new Error('Error of add subscription.'));
					$mainResult->addErrors($subscribeResult->getErrors());
				}
			}
		}

		return $mainResult;
	}

	/**
	 * @param SectionConnection $sectionLink
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function importSectionEvents(SectionConnection $sectionLink): Result
	{
		$mainResult = new Result();
		$resultData = [
			'events' => [
				'deleted' => [],
				'imported' => [],
				'updated' => [],
				'stripped' => [],
				'error' => [],
			],
		];

		$pushResult = static function(array $result) use (&$resultData)
		{
			if (empty($result['entityType']))
			{
				return;
			}
			if ($result['entityType'] === 'link')
			{
				$resultData['events'][$result['action']] = $result['entity']->getEvent()->getId();
			}
			elseif ($result['entityType'] === 'eventId')
			{
				$resultData['events'][$result['action']] = $result['entity'];
			}
		};

		$vendorManager = $this->getFactory()->getEventManager();
		foreach ($vendorManager->fetchSectionEvents($sectionLink) as $eventPack)
		{
			$masterId = null;
			foreach ($eventPack as $eventData)
			{
				/** @var Event $event */
				if ($event = $eventData['event'])
				{
					$event->setSection($sectionLink->getSection());
				}
				if ($eventData['type'] === 'deleted')
				{
					$result = $this->deleteInstance($eventData['id']);
					if ($result->isSuccess())
					{
						$resultData['events']['deleted'][] = $result->getData()['eventId'];
						$resultData[$eventData['id']] = 'delete success';
					}
					else
					{
						$resultData['events']['error'][] = $result->getData()['eventId'];
						$resultData[$eventData['id']] = 'delete fail';
					}
				}
				elseif ($eventData['type'] === 'single')
				{
					$result = $this->importEvent(
						$eventData['event'],
						$eventData['id'],
						$eventData['version'],
						[
							'data' => $eventData['data'] ?? null,
						]
					);
					if ($result->isSuccess())
					{
						$pushResult($result->getData());
						$resultData[$eventData['id']] = 'create success';
					}
					else
					{
						$resultData[$eventData['id']] = 'create fail';
					}
				}
				elseif ($eventData['type'] === 'master')
				{
					$result = $this->importEvent(
						$eventData['event'],
						$eventData['id'],
						$eventData['version'],
						[
							'recursionEditMode' => 'all',
							'data' => $eventData['data'] ?? null,
						]);
					if ($result->isSuccess())
					{
						$masterId = $result->getData()['id'];
						$resultData[$eventData['id']] = 'create success';
					}
					else
					{
						$resultData[$eventData['id']] = 'create fail';
					}
				}
				elseif ($eventData['type'] === 'exception')
				{
					if ($masterId === null)
					{
						$resultData[$eventData['id']] = 'create fail: master event not found';
						continue;
					}
					$eventData['event']->setRecurrenceId($masterId);
					$result = $this->importEvent(
						$eventData['event'],
						$eventData['id'],
						$eventData['version'],
						[
							'data' => $eventData['data'] ?? null,
						]
					);
					if ($result->isSuccess())
					{
						$masterId = $result->getData()['id'];
						$resultData[$eventData['id']] = 'create success';
					}
					else
					{
						$resultData[$eventData['id']] = 'create fail';
					}
				}
			}
		}

		return $mainResult->setData($resultData);
	}

	/**
	 * @param string $vendorId
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	private function deleteInstance(string $vendorId): Result
	{
		$result = new Result();
		try
		{
			$linkData = EventConnectionTable::query()
				->setSelect([
					...EventConnectionTable::defaultSelect,
					'EVENT',
				])
				->addFilter('CONNECTION_ID', $this->connection->getId())
				->addFilter('=VENDOR_EVENT_ID', $vendorId)
				->exec()->fetchObject()
			;
		}
		catch (\Bitrix\Main\ArgumentException $exception)
		{
			$result->addError(new Error('Probably corrupted data'));

			return $result;
		}

		if ($linkData)
		{
			if (!CCalendarEvent::Delete([
				'id' => $linkData->getEventId(),
				'userId' => $this->connection->getOwner()->getId(),
				'bMarkDeleted' => true,
				'originalFrom' => $this->connection->getVendor()->getCode(),
			]))
			{
				$result->addError(new Error('Error of delete event'));
				$result->setData(['eventId' => $linkData->getEventId()]);
			}
		}
		else
		{
			$result->addError(new Error('Event not found'));
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @param string $vendorId
	 * @param string|null $vendorVersion
	 * @param array $params
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 * @throws Exception
	 */
	private function importEvent(
		Event $event,
		string $vendorId,
		string $vendorVersion = null,
		array $params = []
	): Result
	{
		$prepareResult = function (string $action, string $entityType, $entity)
		{
			return [
				'type' => $entityType,
				'action' => $action,
				'value' => $entity,
			];
		};

		$result = new Result();

		$mapper = new Core\Mappers\Event();

		$link = (new EventConnectionFactory())->getEventConnection([
			'filter' => [
				'CONNECTION_ID' => $this->connection->getId(),
				'=VENDOR_EVENT_ID' => $vendorId,
			],
			'connection' => $this->connection,
		]);
		// TODO: explode to 2 methods
		if ($link)
		{
			if ($vendorVersion && $link->getEntityTag() === $vendorVersion)
			{
				$resultData = $prepareResult('skipped', 'link', $link);
			}
			elseif (
				empty($event->getDateModified())
				|| empty($link->getEvent()->getDateModified())
				|| ($link->getEvent()->getDateModified()->getTimestamp() >= $event->getDateModified()->getTimestamp())
			)
			{
				$resultData = $prepareResult('skipped', 'link', $link);
			}
			else
			{
				try {
					$event->setId($link->getEvent()->getId());
					$event->setOwner(Core\Role\Helper::getRole(
							$this->connection->getOwner()->getId(),
							$this->connection->getOwner()->getType(),
					));
					$this->mergeReminders($event, $link->getEvent());
					$mapper->update($event, [
						'originalFrom' => $this->connection->getVendor()->getCode(),
						'userId' => $this->connection->getOwner()->getId(),
					]);
					EventConnectionTable::update($link->getId(), [
						'ENTITY_TAG' => $vendorVersion,
						'DATA' => $params['data'] ?? null,
					]);
					$resultData = $prepareResult('updated', 'link', $link);

				}
				catch (Exception $e) {
					$resultData = $prepareResult('error', 'link', $link);
					$result->addError(new Error($e->getMessage(), $e->getCode()));
				}
			}
		}
		else
		{
			try
			{
				$event->setOwner(Core\Role\Helper::getRole(
					$this->connection->getOwner()->getId(),
					$this->connection->getOwner()->getType(),
				));
				$newEvent = $mapper->create($event, [
					'originalFrom' => $this->connection->getVendor()->getCode(),
					'userId' => $this->connection->getOwner()->getId(),
				]);
				$linkResult = EventConnectionTable::add([
					'EVENT_ID' => $newEvent->getId(),
					'CONNECTION_ID' => $this->connection->getId(),
					'VENDOR_EVENT_ID' => $vendorId,
					'SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
					'ENTITY_TAG' => $vendorVersion,
					'VERSION' => 1,
					'DATA' => $params['data'] ?? null,
				]);
				if ($linkResult->isSuccess())
				{
					$resultData = $prepareResult('imported', 'eventId', $newEvent->getId());
				}
				else
				{
					$resultData = $prepareResult('error', 'eventId', $newEvent->getId());
					$result->addError(reset($linkResult->getErrors()));
				}
			}
			catch (Exception $e)
			{
				$resultData = $prepareResult('error', 'vendorId', $vendorId);
				$result->addError(new Error($e->getMessage()));
			}
		}

		return $result->setData($resultData ?? []);
	}

	/**
	 * @return VendorSynchronization
	 *
	 * @throws ObjectNotFoundException
	 */
	private function getSyncManager(): VendorSynchronization
	{
		if (empty($this->syncManager))
		{
			$this->syncManager = new VendorSynchronization($this->getFactory());
		}

		return $this->syncManager;
	}

	private function mergeSections(Section $baseSection, Section $newSection): Section
	{
		$baseSection
			->setId($newSection->getId() ?: $baseSection->getId())
			->setName($newSection->getName() ?: $baseSection->getName())
			->setDescription($newSection->getDescription())
			->setIsActive($newSection->isActive())
			->setColor($newSection->getColor())
			;
		return $baseSection;
	}

	/**
	 * @param Section $section
	 *
	 * @return Section|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function saveSection(Section $section): ?Section
	{
		if ($this->connection->getOwner() === null)
		{
			throw new Core\Base\BaseException('The connection must have an owner');
		}

		$fields = [
			'NAME' => $section->getName(),
			'ACTIVE' => 'Y',
			'DESCRIPTION' => $section->getDescription() ?? '',
			'COLOR' => $section->getColor() ?? '',
			'CAL_TYPE' => $this->connection->getOwner()->getType(),
			'OWNER_ID' => $this->connection->getOwner()->getId(),
			'CREATED_BY' => $this->connection->getOwner()->getId(),
			'DATE_CREATE' => new DateTime(),
			'TIMESTAMP_X' => new DateTime(),
			'EXTERNAL_TYPE' => $this->connection->getVendor()->getCode(),
		];
		$sectionId = CCalendar::SaveSection([
			'arFields' => $fields,
			'originalFrom' => $this->connection->getVendor()->getCode(),
		]);

		if ($sectionId)
		{
			return $this->mapperFactory->getSection()
				->resetCacheById($sectionId)
				->getById($sectionId)
			;
		}

		return null;
	}

	/**
	 * @param Event $targetEvent
	 * @param Event $sourceEvent
	 *
	 * @return void
	 */
	private function mergeReminders(Event $targetEvent, Event $sourceEvent)
	{
		foreach ($sourceEvent->getRemindCollection()->getCollection() as $item)
		{
			$targetEvent->getRemindCollection()->add($item);
		}
	}

	/**
	 * @param Section $section
	 * @param int $linkId
	 *
	 * @return void
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 * @todo Maybe find other place for this method
	 */
	public function deleteSection(Section $section, int $linkId)
	{
		$this->deleteSectionConnection($section->getId(), $linkId);

		if ($section->getExternalType() !== 'local')
		{
			CCalendarSect::Delete($section->getId(), false, [
				'originalFrom' => $this->connection->getVendor()->getCode(),
			]);
		}
		else
		{
			$this->exportLocalSection($section);
		}
	}

	/**
	 * @param int $sectionId
	 * @param int $sectionConnectionId
	 *
	 * @return void
	 * @throws Exception
	 */
	private function deleteSectionConnection(int $sectionId, int $sectionConnectionId)
	{
		global $DB;

		if ($this->connection->getId() && $sectionId)
		{
			$DB->Query("
				DELETE FROM b_calendar_event_connection
				WHERE CONNECTION_ID = " . $this->connection->getId()  . "  
				AND EVENT_ID IN (SELECT EV.ID FROM b_calendar_event EV
		        WHERE EV.SECTION_ID = " . $sectionId . " );"
			);
		}

		if ($sectionConnectionId)
		{
			SectionConnectionTable::delete($sectionConnectionId);
		}
	}

	/**
	 * @param Section $section
	 *
	 * @return void
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function exportLocalSection(Section $section): void
	{
		$manager = new OutgoingManager($this->connection);
		$sectionResult = $manager->exportSectionSimple($section);
		if ($sectionResult->isSuccess() && $sectionResult->getData()['exported'])
		{
			/** @var SectionConnection $sectionLink */
			$sectionLink = $sectionResult->getData()['exported'];
			$manager->exportSectionEvents($sectionLink);
		}
	}
}
