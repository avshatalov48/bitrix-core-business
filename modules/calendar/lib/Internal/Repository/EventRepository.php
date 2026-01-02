<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Internal\Repository;

use Bitrix\Calendar\Core\Builders\EventBuilderFromEntityObject;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Internals\EO_Event;
use Bitrix\Calendar\Internals\EO_SectionConnection;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Sync\Builders\BuilderEventConnectionFromDM;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Entities\SyncEventMap;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SystemException;

class EventRepository
{
	private Factory $mapperFactory;

	public function __construct()
	{
		// @todo Remove old dependency!
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	/**
	 * @return Event[]
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function getExportingEventsBySectionIds(array $sectionIds, int $userId, int $connectionId): array
	{
		if (!$sectionIds)
		{
			return [];
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		Loader::includeModule('dav');

		$timestamp = time() - 2600000;

		$eventDb = EventTable::query()
			->setSelect([
				'*',
				'EVENT_CONNECTION.ID',
				'EVENT_CONNECTION.EVENT_ID',
				'EVENT_CONNECTION.CONNECTION_ID',
				'EVENT_CONNECTION.VENDOR_EVENT_ID',
				'EVENT_CONNECTION.SYNC_STATUS',
				'EVENT_CONNECTION.RETRY_COUNT',
				'EVENT_CONNECTION.ENTITY_TAG',
				'EVENT_CONNECTION.VERSION',
				'EVENT_CONNECTION.VENDOR_VERSION_ID',
				'EVENT_CONNECTION.RECURRENCE_ID',
				'EVENT_CONNECTION.CONNECTION',
				'EVENT_CONNECTION.EVENT',
			])
			->where('OWNER_ID', $userId)
			->where('CAL_TYPE', \Bitrix\Calendar\Core\Event\Tools\Dictionary::CALENDAR_TYPE['user'])
			->where('DATE_TO_TS_UTC', '>', $timestamp)
			->where(Query::filter()
				->logic('or')
				->whereNot('MEETING_STATUS', 'N')
				->whereNull('MEETING_STATUS'),
			)
			->whereIn('SECTION_ID', $sectionIds)
			->registerRuntimeField('EVENT_CONNECTION',
				new ReferenceField(
					'SYNC_DATA',
					EventConnectionTable::getEntity(),
					Join::on('ref.EVENT_ID', 'this.ID')
						->where('ref.CONNECTION_ID', $connectionId)
					,
					['join_type' => Join::TYPE_LEFT],
				),
			)
			->addOrder('ID')
			->exec()
		;

		$map = new SyncEventMap();

		$impatientSyncEventInstanceList = [];

		while ($eventDM = $eventDb->fetchObject())
		{
			$syncEvent = new SyncEvent();

			$event = (new EventBuilderFromEntityObject($eventDM))->build();
			$syncEvent->setEvent($event);

			$action = $this->getActionByEventConnection($eventDM, $event);

			if ($syncEvent->isInstance())
			{
				$syncEvent->setAction($action);
				/** @var SyncEvent $masterEvent */
				$masterEvent = $map->getItem($event->getRecurrenceId());

				if ($masterEvent)
				{
					if (
						$masterEvent->getAction() === Dictionary::SYNC_EVENT_ACTION['success']
						&& $syncEvent->getAction() !== Dictionary::SYNC_EVENT_ACTION['success']
					)
					{
						$masterEvent->setAction(Dictionary::SYNC_EVENT_ACTION['update']);
					}

					$masterEvent->addInstance($syncEvent);

					continue;
				}

				$impatientSyncEventInstanceList[$event->getRecurrenceId()][] = $syncEvent;
			}
			else
			{
				if ($instanceList = ($impatientSyncEventInstanceList[$event->getId()] ?? null))
				{
					$syncEvent->addInstanceList($instanceList);

					if (
						$syncEvent->getAction() !== Dictionary::SYNC_EVENT_ACTION['success']
						&& $this->hasCandidatesForUpdate($instanceList)
					)
					{
						$action = Dictionary::SYNC_EVENT_ACTION['update'];
					}

					unset($impatientSyncEventInstanceList[$event->getId()]);
				}
				$syncEvent->setAction($action);
				$map->add($syncEvent, $event->getId());
			}
		}

		return $this->getExportingEvents($map);
	}

	/**
	 * @param SyncEvent[] $instances
	 *
	 * @return bool
	 */
	private function hasCandidatesForUpdate(array $instances): bool
	{
		foreach ($instances as $instance)
		{
			if ($instance->getAction() !== Dictionary::SYNC_EVENT_ACTION['success'])
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return Event[]
	 */
	private function getExportingEvents(SyncEventMap $map): array
	{
		$events = [];

		/** @var SyncEvent $syncEvent */
		foreach ($map as $syncEvent)
		{
			if ($syncEvent->getAction() === Dictionary::SYNC_EVENT_ACTION['success'])
			{
				continue;
			}

			$events[] = $syncEvent->getEvent();

			if ($syncEvent->hasInstances())
			{
				/** @var SyncEvent $item */
				foreach ($syncEvent->getInstanceMap() as $item)
				{
					$events[] = $item->getEvent();
				}
			}
		}

		return $events;
	}

	private function getActionByEventConnection(EO_Event $eventDM, Event $event): string
	{
		/** @var EO_SectionConnection $sectionConnectionDM */
		if ($eventConnectionDM = $eventDM->get('EVENT_CONNECTION'))
		{
			if ($event->isDeleted())
			{
				return Dictionary::SYNC_EVENT_ACTION['delete'];
			}

			$eventConnection = (new BuilderEventConnectionFromDM($eventConnectionDM))->build();

			$status = $eventConnection->getLastSyncStatus();

			if (
				in_array($status, Dictionary::SYNC_EVENT_ACTION, true)
				&& ($status !== Dictionary::SYNC_EVENT_ACTION['success'])
			)
			{
				return $eventConnection->getLastSyncStatus();
			}

			if ($event->getVersion() > $eventConnection->getVersion())
			{
				return Dictionary::SYNC_EVENT_ACTION['update'];
			}

			return Dictionary::SYNC_EVENT_ACTION['success'];
		}

		if ($event->isDeleted())
		{
			return Dictionary::SYNC_EVENT_ACTION['success'];
		}

		return Dictionary::SYNC_EVENT_ACTION['create'];
	}

	/**
	 * @throws RepositoryReadException
	 */
	public function getMasterEvent(Event $event): ?Event
	{
		try
		{
			return $this->mapperFactory
				->getEvent()
				->getMap([
					'=PARENT_ID' => $event->getRecurrenceId(),
					'=OWNER_ID' => (int)$event->getOwner()?->getId(),
				])
				->fetch()
			;
		}
		catch (SystemException $e)
		{
			throw new RepositoryReadException($e->getMessage(), $e->getCode(), $e);
		}
	}

	/**
	 * @return Event[]
	 *
	 * @throws RepositoryReadException
	 */
	public function getEventInstances(Event $event): array
	{
		try
		{
			$queryResult = EventTable::query()
				->setSelect(['*'])
				->where('RECURRENCE_ID', $event->getParentId())
				->where('DELETED', 'N')
				->where('OWNER_ID', $event->getOwner()->getId())
				->where(
					Query::filter()
						->logic('or')
						->whereNot('MEETING_STATUS', 'N')
						->whereNull('MEETING_STATUS'),
				)
				->whereNotNull('ORIGINAL_DATE_FROM')
				->exec()
			;

			$items = [];

			while ($row = $queryResult->fetchObject())
			{
				if ($eventEntity = $this->mapperFactory->getEvent()->getByEntityObject($row))
				{
					$items[] = $eventEntity;
				}
			}

			return $items;
		}
		catch (SystemException $e)
		{
			throw new RepositoryReadException($e->getMessage(), $e->getCode(), $e);
		}
	}
}
