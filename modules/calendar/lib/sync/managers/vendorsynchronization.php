<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Exceptions\NotFoundException;
use Bitrix\Calendar\Sync\Factories\FactoryInterface;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
use Bitrix\Calendar\Sync\Factories\PushFactoryInterface;
use Bitrix\Calendar\Sync\Icloud;
use Bitrix\Calendar\Sync\Push\Push;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Sync\Util\EventContext;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Calendar\Sync\Util\SectionContext;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Exception;

class VendorSynchronization
{
	/**
	 * @var FactoryInterface|PushFactoryInterface
	 */
	private FactoryInterface $factory;
	/**
	 * @var mixed
	 */
	private Core\Mappers\Factory $mapperFactory;

	/**
	 * @param FactoryInterface $factory
	 *
	 * @throws ObjectNotFoundException
	 */
	public function __construct(FactoryInterface $factory)
	{
		$this->factory = $factory;
		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws SystemException
	 */
	public function createEvent(Event $event, EventContext $context): Result
	{
		$mainResult = new Result();
		$data = [];
		$factory = $this->factory;
		$manager = $factory->getEventManager();

		$sectionLink = $context->getSectionConnection();

		if (!$sectionLink)
		{
			return $mainResult->setData([$factory->getConnection()->getVendor()->getCode() => [
				'status' => Dictionary::SYNC_STATUS['failed'],
				'message' => 'Section link not found', // TODO: Localize?
			]]);
		}

		$eventLink = $this->getEventConnection($event)
				?? (new EventConnection())
					->setEvent($event)
					->setConnection($sectionLink->getConnection());
		if ($sectionLink->isActive())
		{
			try
			{
				$result = $manager->create($event, $context);
				if ($result->isSuccess())
				{
					$resultData = $result->getData();
					$status = Dictionary::SYNC_STATUS['success'];
					$eventLink
						->setVendorEventId($resultData['event']['id'])
						->setEntityTag($resultData['event']['etag'])
						->setVendorVersionId($resultData['event']['version'] ?? null)
						->setVersion($event->getVersion())
						->setLastSyncStatus($status);
					if (!empty($result->getData()['data']))
					{
						$eventLink->setData($result->getData()['data']);
					}
				}
				else
				{
					$status = Dictionary::SYNC_STATUS['create'];
					$eventLink->setLastSyncStatus($status);
				}
				$syncResult = [
					'status' => $status,
					'result' => $result,
				];
			}
			catch (NotFoundException $e)
			{
				// if section was not found on service
				$sectionLink->setActive(false)->setSyncToken(null)->setPageToken(null);
				$this->mapperFactory->getSectionConnection()->update($sectionLink);
			}
		}
		// dont change to "else", because active status could change in the if section
		if (!$sectionLink->isActive())
		{
			$status = Dictionary::SYNC_STATUS['create'];
			$eventLink->setLastSyncStatus($status)->setVendorEventId('');

			$syncResult = [
				'status' => $status,
			];
		}
		/** @var EventConnection $eventLink */
		$eventLink = $eventLink->getId()
			? $this->mapperFactory->getEventConnection()->update($eventLink)
			: $this->mapperFactory->getEventConnection()->create($eventLink);

		$syncResult['eventConnectionId'] = $eventLink->getId();
		$data['result'] = $syncResult;

		return $mainResult->setData($data);
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	public function updateEvent(Event $event, EventContext $context): Result
	{
		$mainResult = new Result();
		$resultData = [];
		$factory = $this->factory;
		$sectionLink = $context->getSectionConnection();

		if (!$sectionLink)
		{
			// TODO: this condition must be checked before call this method
			$mainResult->addError(new Error('Section connection not found'));
			return $mainResult;
		}
		$manager = $factory->getEventManager();
		$eventLink = $context->getEventConnection();

		if ($eventLink && $eventLink->getVendorEventId() && $sectionLink->isActive())
		{
			if ((int)$eventLink->getEventVersion() !== $event->getVersion())
			{
				try
				{
					$result = $manager->update($event, $context);
					if ($result->isSuccess())
					{
						$status = Dictionary::SYNC_STATUS['success'];
						$eventLink
							->setEntityTag($result->getData()['event']['etag'])
							->setVendorVersionId($result->getData()['event']['version'] ?? null)
							->setVersion($event->getVersion());
					}
					else
					{
						$status = Dictionary::SYNC_STATUS['update'];
					}
					$eventLink->setLastSyncStatus($status);

					$this->mapperFactory->getEventConnection()->update($eventLink);
				}
				catch (NotFoundException $e)
				{
					$this->mapperFactory->getEventConnection()->delete($eventLink);

					return $this->createEvent($event, $context);
				}
			}
			else
			{
				$status = Dictionary::SYNC_STATUS['success'];
				$eventLink->setLastSyncStatus($status);
				$this->mapperFactory->getEventConnection()->update($eventLink);
			}

		}
		elseif ($eventLink && !$sectionLink->isActive())
		{
			$status = Dictionary::SYNC_STATUS['update'];
			$eventLink
				->setVersion($event->getVersion())
				->setLastSyncStatus($status)
			;
			$this->mapperFactory->getEventConnection()->update($eventLink);
		}
		elseif (!$eventLink)
		{
			$status = Dictionary::SYNC_STATUS['create'];
			$eventLink = (new EventConnection())
				->setEvent($event)
				->setConnection($sectionLink->getConnection())
				->setLastSyncStatus($status)
				->setVersion($event->getVersion())
			;
			$this->mapperFactory->getEventConnection()->create($eventLink);
		}
		else
		{
			$status = Dictionary::SYNC_STATUS['create'];
		}
		$resultData[$factory->getConnection()->getVendor()->getCode()] = [
			'status' => $status,
		];

		return $mainResult->setData($resultData);
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 * @todo optimize code
	 */
	public function deleteEvent(Event $event, EventContext $context): Result
	{
		global $DB;
		$mainResult = new Result();
		$resultData = [];
		$factory = $this->factory;
		$sectionLink = $context->getSectionConnection();

		if (!$sectionLink)
		{
			// TODO: can't be, maybe need log
			$mainResult->addError(new Error('Section connection not found'));
			return $mainResult;
		}

		$manager = $factory->getEventManager();
		$eventLink = $context->getEventConnection();
		if ($eventLink && $eventLink->getVendorEventId() && $sectionLink->isActive())
		{
			try
			{
				$result = $manager->delete($event, $context);
				if ($result->isSuccess())
				{
					if ($event->getRecurringRule())
					{
						$childToDelete = [];
						$childIds = EventConnectionTable::query()
							->setSelect(['ID', 'EVENT'])
							->where('EVENT.RECURRENCE_ID', $event->getParentId())
							->where('EVENT.OWNER_ID', $event->getOwner()->getId())
							->exec()
						;
						while ($child = $childIds->fetch())
						{
							$childToDelete[] = $child['ID'];
						}
						if ($childToDelete)
						{
							$DB->Query("DELETE FROM b_calendar_event_connection 
								WHERE ID IN (" . implode(',', $childToDelete) . ")
				            	AND CONNECTION_ID = '{$factory->getConnection()->getId()}';
				            ");
						}

					}

					$status = Dictionary::SYNC_STATUS['success'];
					$this->mapperFactory->getEventConnection()->delete($eventLink);
				}
				else
				{
					$status = Dictionary::SYNC_STATUS['delete'];
					$eventLink->setLastSyncStatus($status);
					$this->mapperFactory->getEventConnection()->update($eventLink);
				}
			}
			catch (NotFoundException $e)
			{
				$status = Dictionary::SYNC_STATUS['delete'];
				$this->mapperFactory->getEventConnection()->delete($eventLink);
			}
		}
		elseif ($eventLink && !$sectionLink->isActive())
		{
			$status = Dictionary::SYNC_STATUS['delete'];
			$eventLink->setLastSyncStatus($status);
			$this->mapperFactory->getEventConnection()->update($eventLink);
		}
		else
		{
			$status = Dictionary::SYNC_STATUS['success'];
		}
		$resultData[$factory->getConnection()->getVendor()->getCode()] = [
			'status' => $status,
		];

		return $mainResult->setData($resultData);
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws SystemException
	 * @throws Exception
	 */
	public function createInstance(Event $event, EventContext $context): Result
	{
		$resultData = [];
		$result = new Result();
		$factory = $this->factory;

		$sectionLink = $context->getSectionConnection();

		if (!$sectionLink)
		{
			$result->addError(new Error('Section connection not found'));

			return $result;
		}

		/** @var Event $masterEvent */
		if ($masterEvent = $this->getMasterEvent($event))
		{
			$manager = $factory->getEventManager();
			$masterLink = $this->getEventConnection($masterEvent);
			if ($masterLink === null)
			{
				// error of Transition period. This situation is impossible in regular mode.
				$result->addError(new Error('Series master event does not have connection with vendor'));

				return $result;
			}
			$context->setEventConnection($masterLink);

			$result = $manager->createInstance($event, $context);

			if ($result->isSuccess())
			{
				$resultData = $result->getData();
				$status = Dictionary::SYNC_STATUS['success'];
				if ($factory->getCode() === Icloud\Helper::ACCOUNT_TYPE)
				{
					$masterLink
						->setVendorEventId($result->getData()['event']['id'])
						->setEntityTag($resultData['event']['etag'])
						->setLastSyncStatus($status)
						->setVersion($masterEvent->getVersion())
					;
					$this->mapperFactory->getEventConnection()->update($masterLink);
				}

				$eventLink = (new EventConnection())
					->setEvent($event)
					->setConnection($sectionLink->getConnection())
					->setVendorEventId($result->getData()['event']['id'])
					->setVendorVersionId($result->getData()['event']['version'] ?? null)
					->setRecurrenceId($result->getData()['event']['recurrence'] ?? null)
					->setData($result->getData()['event']['data'] ?? null)
					->setLastSyncStatus($status)
					->setVersion($event->getVersion())
				;
				if ($factory->getCode() !== Icloud\Helper::ACCOUNT_TYPE)
				{
					$eventLink->setEntityTag($result->getData()['event']['etag'] ?? null);
				}

				$this->mapperFactory->getEventConnection()->create($eventLink);
			}
			else
			{
				$status = Dictionary::SYNC_STATUS['failed'];
				$masterLink->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);
				$this->mapperFactory->getEventConnection()->update($masterLink);
			}
		}
		else
		{
			$status = Dictionary::SYNC_STATUS['failed'];
			$result->addError(new Error('Master event not found'));
		}

		$resultData[$factory->getConnection()->getVendor()->getCode()] = [
			'result' => $result,
			'status' => $status,
		];

		return (new Result())->setData($resultData);
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	public function updateInstance(Event $event, EventContext $context): Result
	{
		$result = new Result();
		$resultData = [];
		$factory = $this->factory;
		$sectionLink = $context->getSectionConnection();
		$eventLink = $context->getEventConnection();
		if (!$sectionLink)
		{
			$result->addError(new Error('Section connection not found'));

			return $result;
		}
		if (!$eventLink)
		{
			$result->addError(new Error('Instance connection not found'));

			return $result;
		}

		if ($masterEvent = $this->getMasterEvent($event))
		{
			$manager = $factory->getEventManager();
			$masterLink = $this->getEventConnection($masterEvent);

			if ($masterLink === null)
			{
				// error of Transition period. This situation is impossible in regular mode.
				$result->addError(new Error('Series master event does not have connection with vendor'));

				return $result;
			}

			$context
				->setEventConnection($masterLink)
				->add('sync', 'instanceLink', $eventLink)
			;

			$result = $manager->updateInstance($event, $context);
			if ($result->isSuccess())
			{
				$status = Dictionary::SYNC_STATUS['success'];
				$eventLink
					->setLastSyncStatus($status)
					->setVersion($event->getVersion())
					->setVendorVersionId($result->getData()['event']['version'] ?? null)
				;
				if (!empty($result->getData()['event']['id']))
				{
					$eventLink->setVendorEventId($result->getData()['event']['id']);
				}
				if ($factory->getCode() !== Icloud\Helper::ACCOUNT_TYPE)
				{
					$eventLink->setEntityTag($result->getData()['event']['etag'] ?? null);
				}
				$this->mapperFactory->getEventConnection()->update($eventLink);

				if ($factory->getCode() === Icloud\Helper::ACCOUNT_TYPE)
				{
					$masterLink
						->setEntityTag($result->getData()['event']['etag'])
						->setLastSyncStatus($status)
						->setVersion($masterEvent->getVersion())
					;
					$this->mapperFactory->getEventConnection()->update($masterLink);
				}
			}
			else
			{
				$status = Dictionary::SYNC_STATUS['failed'];
				$masterLink->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);
				$this->mapperFactory->getEventConnection()->update($masterLink);
			}
		}
		else
		{
			$status = Dictionary::SYNC_STATUS['failed'];
			$result->addError(new Error('Master event not found'));
		}

		$resultData[$factory->getConnection()->getVendor()->getCode()] = [
			'result' => $result,
			'status' => $status,
		];

		return (new Result())->setData($resultData);
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws \Exception
	 */
	public function deleteInstance(Event $event, EventContext $context): Result
	{
		$mainResult = new Result();
		$result = new Result();
		$resultData = [];
		$factory = $this->factory;
		$excludeDate = $context->sync['excludeDate'] ?? null;

		if (!$excludeDate)
		{
			$mainResult->addError(new Error('Not found info about exclude date'));
			return $mainResult;
		}
		$sectionLink = $context->getSectionConnection();
		$masterLink = $context->getEventConnection();

		if ($masterLink && $sectionLink)
		{
			$manager = $factory->getEventManager();
			$result = $manager->deleteInstance($event, $context);
			if ($result->isSuccess())
			{
				$status = Dictionary::SYNC_STATUS['success'];
				$masterLink
					->setEntityTag($result->getData()['event']['etag'] ?? null)
					->setLastSyncStatus($status)
					->setVersion($masterLink->getEvent()->getVersion())
				;
			}
			else
			{
				$status = Dictionary::SYNC_STATUS['failed'];
				$masterLink->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);
			}

			$this->mapperFactory->getEventConnection()->update($masterLink);
		}
		else
		{
			$status = Dictionary::SYNC_STATUS['failed'];
			$result->addError(new Error('Link not found'));
		}

		$resultData[$factory->getConnection()->getVendor()->getCode()] = [
			'result' => $result,
			'status' => $status,
		];

		return $result->setData($resultData);
	}

	/**
	 * @param SyncEvent $recurrenceEvent
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws Exception
	 */
	public function createRecurrence(SyncEvent $recurrenceEvent, EventContext $context): Result
	{
		$mainResult = new Result();
		$resultData = [];
		$factory = $this->factory;
		$manager = $factory->getEventManager();
		/** @var SectionConnection $sectionLink */
		$sectionLink = $context->getSectionConnection();
		if (!$sectionLink)
		{
			$mainResult->addError(new Error('Section connection not found'));

			return $mainResult;
		}
		$context->add('sync', 'vendorSectionId', $sectionLink->getVendorSectionId());

		$recurrenceEvent->getEvent()->setUid(null);

		$result = $manager->createRecurrence($recurrenceEvent, $sectionLink, $context);

		if ($result->isSuccess())
		{
			$masterResult = $this->createEventLink($recurrenceEvent, $this->factory->getConnection()->getId());

			/** @var SyncEvent $instance */
			foreach ($recurrenceEvent->getInstanceMap()->getCollection() as $instance)
			{
				$instanceResult[] = $this->createEventLink($instance, $this->factory->getConnection()->getId());
			}

			$status = Dictionary::SYNC_STATUS['success'];
		}

		else
		{
			$status = Dictionary::SYNC_STATUS['failed'];
		}

		$resultData[$factory->getConnection()->getVendor()->getCode()] = [
			'result' => $result,
			'status' => $status,
			'linkMasterResult' => $masterResult ?? null,
			'linkInstancesResult' => $instanceResult ?? null,
		];

		return $mainResult->setData($resultData);
	}

	/**
	 * @param SyncEvent $recurrenceEvent
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws Exception
	 */
	public function updateRecurrence(SyncEvent $recurrenceEvent, EventContext $context): Result
	{
		$mainResult = new Result();
		$resultData = [];
		$factory = $this->factory;
		$manager = $factory->getEventManager();
		/** @var SectionConnection $sectionLink */
		$sectionLink = $context->getSectionConnection();
		if (!$sectionLink)
		{
			$mainResult->addError(new Error('Section connection not found'));

			return $mainResult;
		}
		$context->add('sync', 'vendorSectionId', $sectionLink->getVendorSectionId());

		$recurrenceEvent->getEvent()->setUid(
			$recurrenceEvent->getEventConnection()->getVendorEventId()
		);

		if (
			(int)$recurrenceEvent->getEventConnection()->getEventVersion()
			=== $recurrenceEvent->getEvent()->getVersion()
		)
		{
			$result = new Result();
			$result->setData([
				'event' => [
					'id' => $recurrenceEvent->getEventConnection()->getVendorEventId(),
					'etag' => $recurrenceEvent->getEventConnection()->getEntityTag(),
				],
			]);
			$status = Dictionary::SYNC_STATUS['success'];
		}
		else
		{
			try
			{
				$result = $manager->updateRecurrence($recurrenceEvent, $sectionLink, $context);
				if ($result->isSuccess())
				{
					$masterResult = $this->updateEventLink($recurrenceEvent);
					$instanceResult = [];
					/** @var SyncEvent $instance */
					foreach ($recurrenceEvent->getInstanceMap()->getCollection() as $instance)
					{
						if ($instance->getEventConnection() && $instance->getEventConnection()->getId())
						{
							$instanceResult[] = $this->updateEventLink($instance);
						}
						else
						{
							$instanceResult[] = $this->createEventLink(
								$instance,
								$this->factory->getConnection()->getId()
							);
						}
					}

					$status = Dictionary::SYNC_STATUS['success'];
				}
				else
				{
					$status = Dictionary::SYNC_STATUS['failed'];
				}
			}
			catch (NotFoundException $e)
			{
				$recurrenceEvent->getEvent()->setUid(null);
				return $this->createRecurrence($recurrenceEvent, $context);
			}
		}

		$resultData[$factory->getConnection()->getVendor()->getCode()] = [
			'result' => $result,
			'status' => $status,
			'linkMasterResult' => $masterResult ?? null,
			'linkInstancesResult' => $instanceResult ?? null,
		];

		return $mainResult->setData($resultData);
	}

	/**
	 * @param Section $section
	 * @param SectionContext $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 * @todo optimize this method
	 */
	public function createSection(Section $section, SectionContext $context): Result
	{
		$mainResult = new Result();
		$resultData = [];
		$factory = $this->factory;
		$manager = $factory->getSectionManager();
		$context->add('sync', 'connection', $factory->getConnection());

		if ($sectionLink = $context->getSectionConnection())
		{
			$resultData['sectionConnection'] = $sectionLink;
			$status = Dictionary::SYNC_STATUS['update'];
			// TODO: what to do?
		}
		else
		{
			$safeCreate = static function (Section $section, SectionContext $context) use ($manager)
			{
				$result = new Result();
				$counter = 0;
				$originalName = $section->getName();
				do
				{
					try
					{
						$result = $manager->create($section, $context);
						$success = true;
					}
					catch (ConflictException $e)
					{
						$counter++;
						$section->setName($originalName . " ($counter)");
						$success = false;
					}
				}
				while (!$success);

				$section->setName($originalName);

				return $result;
			};

			$result = $safeCreate($section, $context);
			if ($result->isSuccess())
			{
				$status = Dictionary::SYNC_STATUS['success'];
				$sectionLink = (new SectionConnection())
					->setSection($section)
					->setConnection($factory->getConnection())
					->setVendorSectionId($result->getData()['id'])
					->setVersionId($result->getData()['version'])
					->setActive(true)
					->setLastSyncStatus($status)
					->setLastSyncDate(new Core\Base\Date())
				;
				$this->mapperFactory->getSectionConnection()->create($sectionLink);

				$resultData['sectionConnection'] = $sectionLink;
			}
			else
			{
				$mainResult->addErrors($result->getErrors());
				$status = Dictionary::SYNC_STATUS['failed'];
			}
		}

		$resultData[$factory->getConnection()->getVendor()->getCode()] = [
			'status' => $status,
		];

		return $mainResult->setData($resultData);
	}

	/**
	 * @param Section $section
	 * @param SectionContext $context
	 *
	 * @return Result
	 *
	 * @throws Core\Base\BaseException
	 */
	public function updateSection(Section $section, SectionContext $context): Result
	{
		$mainResult = new Result();
		$result = new Result();
		$factory = $this->factory;

		if ($sectionLink = $context->getSectionConnection())
		{
			$manager = $factory->getSectionManager();
			if ($sectionLink->isActive())
			{
				try
				{
					$result = $manager->update($section, $context);
					if ($result->isSuccess())
					{
						$status = Dictionary::SYNC_STATUS['success'];
						$sectionLink->setVersionId($result->getData()['version']);
					}
					else
					{
						$status = Dictionary::SYNC_STATUS['update'];
						$mainResult->addErrors($result->getErrors());
					}
				}
				catch (NotFoundException $e)
				{
					$sectionLink->setActive(false);
					$status = Dictionary::SYNC_STATUS['inactive'];
				}

				$sectionLink
					->setLastSyncStatus($status)
					->setLastSyncDate(new Core\Base\Date());
				$this->mapperFactory->getSectionConnection()->update($sectionLink);
			}
			else
			{
				$status = Dictionary::SYNC_STATUS['inactive'];
			}
		}
		else
		{
			$status = Dictionary::SYNC_STATUS['create'];
			// TODO: think, what to do in this case. Call the creation or throw exception
		}

		$resultData = [
			$factory->getConnection()->getVendor()->getCode() => [
				'status' => $status,
			],
			'error' => $result->getData()['error'] ?? '',
		];

		return $mainResult->setData($resultData);
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 * @throws Exception
	 */
	public function deleteSection(Section $section, SectionContext $context): Result
	{
		$mainResult = new Result();
		$resultData = [];
		$factory = $this->factory;
		$manager = $factory->getSectionManager();

		if (($sectionLink = $context->getSectionConnection()) && $sectionLink->isActive())
		{
			$sectionLink->setSection($section);
			$result = $manager->delete($section, $context);
			if ($result->isSuccess())
			{
				$status = Dictionary::SYNC_STATUS['success'];
			}
			else
			{
				$status = Dictionary::SYNC_STATUS['delete'];
				$mainResult->addErrors($result->getErrors());
				$sectionLink
					->setLastSyncStatus($status)
					->setLastSyncDate(new Core\Base\Date())
				;
				$this->mapperFactory->getSectionConnection()->update($sectionLink);
			}
		}
		else
		{
			$status = Dictionary::SYNC_STATUS['delete'];
		}

		$resultData[$factory->getConnection()->getVendor()->getCode()] = [
			'status' => $status,
		];

		return $mainResult->setData($resultData);
	}

	/**
	 * @param Event $event
	 * @param Connection $connection
	 * @param string $version
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 * @todo change signature in the future
	 */
	public function upEventVersion(Event $event, Connection $connection, string $version)
	{
		$link = EventConnectionTable::query()
			->setSelect(['ID'])
			->addFilter('CONNECTION_ID', $connection->getId())
			->addFilter('EVENT_ID', $event->getId())
			->exec()
			->fetchObject()
		;

		if ($link)
		{
			EventConnectionTable::update($link->getId(), [
				'fields' => [
					'VERSION' => $version,
				],
			]);
		}
	}

	/**
	 * @return bool
	 */
	public function canSubscribeSection(): bool
	{
		return $this->factory->canSubscribeSection();
	}

	/**
	 * @return bool
	 */
	public function canSubscribeConnection(): bool
	{
		return $this->factory->canSubscribeConnection();
	}

	/**
	 * @param SectionConnection $link
	 *
	 * @return Result
	 */
	public function subscribeSection(SectionConnection $link): Result
	{
		return $this->factory->getPushManager()->addSectionPush($link);
	}

	/**
	 * @return Result
	 */
	public function subscribeConnection(): Result
	{
		return $this->factory->getPushManager()->addConnectionPush($this->factory->getConnection());
	}

	/**
	 * @param Push $push
	 *
	 * @return Result
	 */
	public function renewPush(Push $push): Result
	{
		if ($manager = $this->factory->getPushManager())
		{
			return $manager->renewPush($push);
		}

		return (new Result())->addError(new Error('Push manager for service not found', 404));
	}

	/**
	 * @param Push $push
	 *
	 * @return Result
	 */
	public function unsubscribeSection(Push $push): Result
	{
		return $this->factory->getPushManager()->deletePush($push);
	}

	/**
	 * @param Push $push
	 *
	 * @return Result
	 */
	public function unsubscribeConnection(Push $push): Result
	{
		return $this->factory->getPushManager()->deletePush($push);
	}

	/**
	 * @param Event $event
	 *
	 * @return EventConnection|null
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws SystemException
	 */
	private function getEventConnection(Event $event): ?EventConnection
	{
		return $this->mapperFactory->getEventConnection()->getMap([
            '=EVENT_ID' => $event->getId(),
            '=CONNECTION_ID' => $this->factory->getConnection()->getId(),
        ])->fetch();
	}

	/**
	 * @param Event $event
	 *
	 * @return Event|null
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws SystemException
	 */
	private function getMasterEvent(Event $event): ?Event
	{
		return $this->mapperFactory->getEvent()->getMap([
			'=PARENT_ID' => $event->getRecurrenceId(),
			'=OWNER_ID' => $event->getOwner()->getId(),
		])->fetch();
	}

	/**
	 * @param SyncEvent $syncEvent
	 * @param int $connectionId
	 *
	 * @return \Bitrix\Main\ORM\Data\AddResult
	 * @throws Exception
	 */
	private function createEventLink(SyncEvent $syncEvent, int $connectionId): \Bitrix\Main\ORM\Data\AddResult
	{
		// TODO: change to mapper->create;
		// $this->mapperFactory->getEventConnection()->create();
		return EventConnectionTable::add([
			'EVENT_ID' => $syncEvent->getEvent()->getId(),
			'CONNECTION_ID' => $connectionId,
			'VENDOR_EVENT_ID' => $syncEvent->getEventConnection()
				? $syncEvent->getEventConnection()->getVendorEventId()
				: null
			,
			'SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
			'VERSION' => $syncEvent->getEvent()->getVersion(),
			'ENTITY_TAG' =>  $syncEvent->getEventConnection()
				? $syncEvent->getEventConnection()->getEntityTag()
				: null
			,
		]);
	}

	/**
	 * @param SyncEvent $syncEvent
	 *
	 * @return \Bitrix\Main\ORM\Data\UpdateResult
	 * @throws Exception
	 */
	private function updateEventLink(SyncEvent $syncEvent): Result
	{
		$newLink = $this->mapperFactory->getEventConnection()->update($syncEvent->getEventConnection());

		return (new Result())->setData(['eventConnection' => $newLink]);
	}
}
