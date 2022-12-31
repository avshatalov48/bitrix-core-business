<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Internals\EO_Event;
use Bitrix\Calendar\Internals\EO_Event_Collection;
use Bitrix\Calendar\Internals\EO_EventConnection;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Entities\InstanceMap;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Exceptions\ApiException;
use Bitrix\Calendar\Sync\Factories\FactoryBuilder;
use Bitrix\Calendar\Sync\Factories\FactoryInterface;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Sync\Util\EventContext;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Calendar\Sync\Util\SectionContext;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use Generator;

class OutgoingManager
{
	const TIME_SLICE = 2600000;

	private Connection $connection;

	private FactoryInterface $factory;

	private VendorSynchronization $syncManager;
	/**
	 * @var Core\Mappers\Factory
	 */
	private Core\Mappers\Factory $mapperFactory;

	/**
	 * @throws ObjectNotFoundException
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
		$context = new Context([
			'connection' => $connection,
		]);
		$this->factory = FactoryBuilder::create($connection->getVendor()->getCode(), $connection, $context);
		$this->syncManager = new VendorSynchronization($this->factory);
		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	/**
	 * @param array $params
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function exportSections(array $params = []): Result
	{
		$mainResult = new Result();
		$resultData = [
			'links' => [
				'exported' => [],
				'updated' => [],
				'skipped' => [],
				'error' => [],
			],
			'exportErr' => [],
		];

		$excludeIds = $params['excludeIds'] ?? [];
		/** @var Core\Section\Section $section */
		foreach ($this->fetchSections($excludeIds) as $section)
		{
			$sectionResult = $this->exportSectionSimple($section);
			if ($sectionResult->isSuccess())
			{
				foreach ($sectionResult->getData() as $key => $link)
				{
					$resultData['links'][$key][] = $link;
				}
				$resultData['exported'][] = $section->getId();
			}
			else
			{
				$mainResult->addErrors($sectionResult->getErrors());
				$resultData['exportErr'][] = $section->getId();
			}
		}

		return $mainResult->setData($resultData);
	}

	/**
	 * @param SectionConnection $sectionLink
	 * @param array $excludeEventIds
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function exportSectionEvents(SectionConnection $sectionLink, array $excludeEventIds = []): Result
	{
		$result = new Result();
		$resultData = [
			'events' => [
				'deleted' => [],
				'exported' => [],
				'updated' => [],
				'stripped' => [],
				'error' => [],
			]
		];

		$pushResult = static function(array $result) use (&$resultData)
		{
			if (empty($result['entityType']) || empty($result['entity']))
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

		foreach ($this->fetchSectionEvents($sectionLink->getSection(), $excludeEventIds) as $eventPack)
		{
			$exportResult = $this->exportEvent($eventPack, $sectionLink);
			if ($exportResult->isSuccess())
			{
				$pushResult($exportResult->getData());
			}
			else
			{
				$id = null;

				if ($eventPack['event'])
				{
					$id = $eventPack['event']->getId();
				}
				else if ($eventPack['master'])
				{
					$id = $eventPack['master']->getId();
				}
				$resultData['events']['error'] = $id;
			}
		}

		return $result->setData($resultData);
	}

	/**
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 *
	 */
	public function export(): Result
	{
		$mainResult = new Result();
		$resultData = [];

		foreach ($this->fetchSections() as $section)
		{
			$sectionResult = $this->exportSection($section);
			if (!$sectionResult->isSuccess())
			{
				continue;
			}

			/** @var SectionConnection $sectionLink */
			$sectionLink = $sectionResult->getData()['sectionConnection'] ?? null;

			if ($sectionLink)
			{
				foreach ($this->fetchSectionEvents($section) as $event)
				{
					$this->exportEvent($event, $sectionLink);
				}
			}
			else
			{
				throw new ObjectPropertyException('Context object das not have sectionLink information');
			}
		}

		return $mainResult->setData($resultData);
	}

	/**
	 * @param Core\Section\Section $section
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 *
	 */
	public function exportSection(Core\Section\Section $section): Result
	{
		$sectionContext = new SectionContext();
		if ($link = $this->getSectionLink($section))
		{
			if ($link->isActive())
			{
				$result = $this->syncManager->updateSection($section, $sectionContext);
				if ($result->isSuccess())
				{
					$result->setData(array_merge($result->getData(), [
						'sectionConnection' => $link,
					]));
				}
				else if (
					!$result->isSuccess()
					&& $result->getData()['error'] === 404
					&& $section->getExternalType() === 'local'
				)
				{
					$this->deleteSectionConnection($link);
					$sectionContext->setSectionConnection(null);
					$result = $this->syncManager->createSection($section, $sectionContext);
				}
			}
			else
			{
				$result = new Result();
			}
		}
		else
		{
			$result = $this->syncManager->createSection($section, $sectionContext);
		}

		return $result;
	}

	/**
	 * @param Core\Section\Section $section
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException //     * @throws SystemException
	 * @throws SystemException
	 * @throws ApiException
	 * @throws Exception
	 */
	public function exportSectionSimple(Core\Section\Section $section): Result
	{
		$resultData = [];
		$mainResult = new Result();
		$sectionContext = new SectionContext();

		if ($link = $this->getSectionLink($section))
		{
			$sectionContext->setSectionConnection($link);
			if ($link->isActive())
			{
				$result = $this->syncManager->updateSection($section, $sectionContext);
				if ($result->isSuccess())
				{
					$resultData['updated'] = $link;
				}
				else
				{
					$resultData['error'] = $link;
				}
			}
			else
			{
				$resultData['skipped'] = $link;
			}
		}
		elseif ($section->getExternalType() !== Core\Section\Section::LOCAL_EXTERNAL_TYPE)
		{
			$resultData['skipped'] = $section;
		}
		else
		{
			$result = $this->syncManager->createSection($section, $sectionContext);
			if (!empty($result->getData()['sectionConnection']))
			{
				$resultData['exported'] = $result->getData()['sectionConnection'];
			}
			else
			{
				$result->addError(new Error('Error of export section'));
				$resultData['section'] = $section;
			}
		}

		return $mainResult->setData($resultData);
	}

	/**
	 * @param array $exclude
	 *
	 * @return Generator|\Bitrix\Calendar\Core\Section\Section[]
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function fetchSections(array $exclude = []): Generator
	{
		$sectionList = SectionTable::getList([
			'filter' => [
				'=CAL_TYPE' => $this->connection->getOwner()->getType(),
				'OWNER_ID' => $this->connection->getOwner()->getId(),
				'EXTERNAL_TYPE' => ['local', $this->connection->getVendor()->getCode()],
				'!ID' => $exclude
			],
			'select' => ['*'],
		]);

		$mapper = new Core\Mappers\Section();
		while ($sectionDM = $sectionList->fetchObject())
		{
			$section = $mapper->getByEntityObject($sectionDM);
			yield $section;
		}
	}

	/**
	 * @param \Bitrix\Calendar\Core\Section\Section $section
	 *
	 * @return SectionConnection|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getSectionLink(\Bitrix\Calendar\Core\Section\Section $section): ?SectionConnection
	{
		$mapper = new Core\Mappers\SectionConnection();
		$map = $mapper->getMap([
			'=SECTION_ID' => $section->getId(),
			'=CONNECTION_ID' => $this->connection->getId(),
		]);
		switch ($map->count())
		{
			case 0:
				return null;
			case 1:
				return $map->fetch();
			default:
				throw new Core\Base\BaseException('More than 1 SectionConnections found.');
		}
	}

	/**
	 * @return FactoryInterface
	 */
	private function getFactory(): FactoryInterface
	{
		return $this->factory;
	}

	/**
	 * @param Core\Section\Section $section
	 * @param array $excludeEventIds
	 *
	 * @return Generator|Event[]
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function fetchSectionEvents(Core\Section\Section $section, array $excludeEventIds = []): Generator
	{
		$timestamp = time() - self::TIME_SLICE;
		$eventList = EventTable::getList([
			'select' => [
				'*',
				'LINK.*',
				'LINK.CONNECTION',
			],
			'filter' => [
				'=SECTION_ID' => $section->getId(),
				'=DELETED' => 'N',
				'!ID' => $excludeEventIds,
				'>DATE_TO_TS_UTC' => $timestamp,
				'!=MEETING_STATUS' => 'N',
			],
			'runtime' => [
				new ReferenceField(
					'LINK',
					EventConnectionTable::class,
					[
						'=this.ID' => 'ref.EVENT_ID',
						'ref.CONNECTION_ID' => ['?', $this->connection->getId()],
					],
					['join_type' => 'LEFT']
				),
			],
		])->fetchCollection();

		$eventList = $this->prepareEvents($eventList);

		foreach ($eventList as $eventPack)
		{
			yield $eventPack;
		}
	}

	private function prepareEvents(EO_Event_Collection $events): array
	{
		$result = [];

		foreach ($events as $event)
		{
			if ($event->getRrule())
			{
				$recId = $event->getParentId();
				$result[$recId]['type'] = 'recurrence';
				$result[$recId]['master'] = $event;
			}
			else if ($event->getRecurrenceId())
			{
				$result[$event->getRecurrenceId()]['instances'][] = $event;
			}
			else
			{
				$result[$event->getId()] = [
					'type' => 'single',
					'event' => $event,
				];
			}
		}

		return $result;
	}

	/**
	 * @param EO_Event $eventEntity
	 *
	 * @return EventConnection|null
	 * @throws ArgumentException
	 */
	private function getEventLink(EO_Event $eventEntity): ?EventConnection
	{
		/** @var EO_EventConnection $link */
		if ($link = $eventEntity->get('LINK'))
		{
			$link->setEvent($eventEntity);

			return $this->mapperFactory->getEventConnection()->getByEntityObject($link);
		}

		return null;
	}

	/**
	 * @param array $eventPack
	 * @param SectionConnection $sectionLink
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	private function exportEvent(array $eventPack, SectionConnection $sectionLink): Result
	{
		if (!empty($eventPack['master']))
		{
			return $this->exportRecurrenceEvent($sectionLink, $eventPack);
		}

		if (!empty($eventPack['event']))
		{
			return $this->exportSimpleEvent($sectionLink, $eventPack['event']);
		}

		return (new Result())->addError(new Error('Unsupported eventpack format'));
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
	 *
	 * @todo move this logic to own separated class
	 */
	public function subscribeSection(SectionConnection $link): Result
	{
		$mainResult = new Result();
		if ($this->syncManager->canSubscribeSection())
		{
			$pushManager = new Sync\Managers\PushManager();
			$subscription = $pushManager->getPush(
				PushManager::TYPE_SECTION_CONNECTION,
				$link->getId()
			);
			if ($subscription && !$subscription->isExpired())
			{
				$result = $this->syncManager->renewPush($subscription);
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
				$subscribeResult = $this->syncManager->subscribeSection($link);
				if ($subscribeResult->isSuccess())
				{
					if ($subscription)
					{
						$pushManager->renewPush($subscription, $subscribeResult->getData());
					}
					else
					{
						try
						{
							$pushManager->addPush(
								'SECTION_CONNECTION',
								$link->getId(),
								$subscribeResult->getData()
							);
						}
						catch(SqlQueryException $e)
						{
							if (
								$e->getCode() === 400
								&& substr($e->getDatabaseMessage(), 0, 6) === '(1062)')
							{
								// there's a race situation
							}
							else
							{
								throw $e;
							}
						}

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
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function subscribeConnection(): Result
	{
		$mainResult = new Result();
		if ($this->syncManager->canSubscribeConnection())
		{
			$pushManager = new Sync\Managers\PushManager();
			$subscription = $pushManager->getPush(
				PushManager::TYPE_CONNECTION,
				$this->connection->getId()
			);

			if ($subscription && !$subscription->isExpired())
			{
				$result = $this->syncManager->renewPush($subscription);
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
				$subscribeResult = $this->syncManager->subscribeConnection();
				if ($subscribeResult->isSuccess())
				{
					if ($subscription !== null)
					{
						$pushManager->renewPush($subscription, $subscribeResult->getData());
					}
					else
					{
						$pushManager->addPush(
							'CONNECTION',
							$this->connection->getId(),
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
	 * @param SectionConnection $link
	 *
	 * @return void
	 * @throws Exception
	 */
	private function deleteSectionConnection(SectionConnection $link): void
	{
		global $DB;

		if ($link->getConnection()->getId() && $link->getSection()->getId())
		{
			$DB->Query("
				DELETE FROM b_calendar_event_connection
				WHERE CONNECTION_ID = " . (int)$link->getConnection()->getId() ." 
				AND EVENT_ID IN (SELECT EV.ID FROM b_calendar_event EV
		        WHERE EV.SECTION_ID = " . (int)$link->getSection()->getId() . ");"
			);
		}

		if ($link->getId())
		{
			SectionConnectionTable::delete($link->getId());
		}
	}

	/**
	 * @param SectionConnection $sectionLink
	 * @param EO_Event $eventData
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function exportSimpleEvent(SectionConnection $sectionLink, EO_Event $eventData): Result
	{
		$result = new Result();
		$context = new Context([]);
		$context->add('sync', 'sectionLink', $sectionLink);

		$event = $this->mapperFactory->getEvent()->getByEntityObject($eventData);

		$eventLink = $this->getEventLink($eventData);
		$eventContext = (new EventContext())
			->merge($context)
			->setSectionConnection($sectionLink)
			->setEventConnection($eventLink)
		;

		if ($eventLink && !$eventLink->getVendorEventId())
		{
			$this->mapperFactory->getEventConnection()->delete($eventLink);
			$eventLink = null;
		}

		if ($event && $eventLink)
		{
			$resultUpdate = $this->syncManager->updateEvent($event, $eventContext);
			$resultData = [
				'entityType' => 'link',
				'entity' => $eventLink,
				'action' => 'updated',
			];
			if (!$resultUpdate->isSuccess())
			{
				$result->addErrors($resultUpdate->getErrors());
			}
		}
		else
		{
			$resultAdd = $this->syncManager->createEvent($event, $eventContext);
			$resultData = [
				'entityType' => 'link',
				'action' => 'exported',
			];
			if ($resultAdd->isSuccess())
			{
				if (!empty($resultAdd->getData()['eventConnectionId']))
				{
					$resultData['entity'] = $this->mapperFactory->getEventConnection()
						->getById($resultAdd->getData()['eventConnectionId'])
					;
				}
				else
				{
					$resultData['entity'] = $this->mapperFactory->getEventConnection()->getMap([
						'=EVENT_ID' => $event->getId(),
						'=CONNECTION_ID' =>  $sectionLink->getConnection()->getId(),
					])->fetch();
				}
			}
			else
			{
				$resultData = [
					'entityType' => 'errorLink',
					'action' => 'exported',
				];
				$result->addErrors($resultAdd->getErrors());
			}
		}

		return $result->setData($resultData);
	}

	/**
	 * @param SectionConnection $sectionLink
	 * @param array $eventData
	 *
	 * @return Result
	 * @throws Exception
	 */
	private function exportRecurrenceEvent(SectionConnection $sectionLink, array $eventData): Result
	{
		$context = new EventContext();
		$context->setSectionConnection($sectionLink);

		$recurrenceEvent = $this->buildRecurrenceEvent($eventData);

		if ($recurrenceEvent->getEventConnection())
		{
			$context->setEventConnection($recurrenceEvent->getEventConnection());
			$result = $this->syncManager->updateRecurrence($recurrenceEvent, $context);
		}
		else
		{
			$result = $this->syncManager->createRecurrence($recurrenceEvent, $context);
		}

		$resultData = [
			'entityType' => 'link',
			'action' => 'updated',
		];

		return $result->setData($resultData);
	}

	/**
	 * @param array $eventData
	 *
	 * @return SyncEvent
	 * @throws ArgumentException
	 */
	private function buildRecurrenceEvent(array $eventData): SyncEvent
	{
		$masterEvent = $this->mapperFactory->getEvent()->getByEntityObject($eventData['master']);
		$masterLink = $this->getEventLink($eventData['master']);
		$masterSyncEvent = (new SyncEvent())
			->setEvent($masterEvent)
			->setEventConnection($masterLink)
		;
		if ($masterSyncEvent->getEventConnection() && !$masterSyncEvent->getEventConnection()->getVendorEventId())
		{
			$this->mapperFactory->getEventConnection()->delete($masterSyncEvent->getEventConnection());
			$masterSyncEvent->setEventConnection(null);
		}

		$instancesCollection = new InstanceMap();
		$instances = $eventData['instances'] ?? [];
		foreach ($instances as $instance)
		{
			$instanceEvent = $this->mapperFactory->getEvent()->getByEntityObject($instance);
			$instanceLink = $this->getEventLink($instance);
			$instanceSyncEvent = (new SyncEvent())
				->setEvent($instanceEvent)
				->setEventConnection($instanceLink)
			;

			if ($instanceSyncEvent->getEventConnection() && !$instanceSyncEvent->getEventConnection()->getVendorEventId())
			{
				$this->mapperFactory->getEventConnection()->delete($instanceSyncEvent->getEventConnection());
				$instanceSyncEvent->setEventConnection(null);
			}
			$instancesCollection->add($instanceSyncEvent);
		}

		$masterSyncEvent->setInstanceMap($instancesCollection);

		return $masterSyncEvent;
	}
}
