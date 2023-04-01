<?php

namespace Bitrix\Calendar\Sync\Factories;

use Bitrix\Calendar\Core\Builders\EventBuilderFromEntityObject;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Internals\EO_SectionConnection;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SystemException;

class SyncEventFactory
{
	const TIME_SLICE = 2600000;

	private Mappers\Event $eventMapper;
	private Mappers\EventConnection $eventConnectionMapper;

	public function __construct()
	{
		$helper = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$this->eventMapper = $helper->getEvent();
		$this->eventConnectionMapper = $helper->getEventConnection();
	}

	/**
	 * @param Sync\Entities\SyncEvent $syncEvent
	 * @return void
	 */
	public function delete(Sync\Entities\SyncEvent $syncEvent): void
	{
		$this->eventMapper->delete($syncEvent->getEvent());
		$this->eventConnectionMapper->delete($syncEvent->getEventConnection());
	}

	/**
	 * @param array $vendorEventIdList
	 * @param int $connectionId
	 * @return Sync\Entities\SyncEventMap
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function getSyncEventCollectionByVendorIdList(
		array $vendorEventIdList,
		int $connectionId
	): Sync\Entities\SyncEventMap
	{
		$syncEventMap = new Sync\Entities\SyncEventMap();
		if (!$vendorEventIdList)
		{
			return $syncEventMap;
		}

		$filter = [
			[
				'logic' => 'or',
				['VENDOR_EVENT_ID', 'in', $vendorEventIdList],
				['RECURRENCE_ID', 'in', $vendorEventIdList],
			],
			['CONNECTION_ID', '=', $connectionId],
		];
		$query = ConditionTree::createFromArray($filter);
		// $params = ['filter' => $query];
		$eventConnectionMap = $this->eventConnectionMapper->getMap($query);

		$impatientExportSyncEventList = [];

		/** @var Sync\Connection\EventConnection $eventConnection */
		foreach ($eventConnectionMap as $eventConnection)
		{
			$syncEvent = new SyncEvent();
			$syncEvent
				->setEventConnection($eventConnection)
				->setEvent($eventConnection->getEvent())
				->setAction($eventConnection->getLastSyncStatus())
			;

			if ($syncEvent->isInstance())
			{
				/** @var SyncEvent $masterSyncEvent */
				if ($masterSyncEvent = $syncEventMap->getItem($syncEvent->getVendorRecurrenceId()))
				{
					$masterSyncEvent->addInstance($syncEvent);
					continue;
				}

				$impatientExportSyncEventList[$syncEvent->getVendorRecurrenceId()][$eventConnection->getVendorEventId()] = $syncEvent;
				continue;
			}

			if (
				$syncEvent->isRecurrence()
				&& $instanceList = ($impatientExportSyncEventList[$syncEvent->getVendorEventId()] ?? null)
			)
			{
				$syncEvent->addInstanceList($instanceList);
			}

			$syncEventMap->add(
				$syncEvent,
				$eventConnection->getVendorEventId()
			);
		}

		if ($impatientExportSyncEventList)
		{
			foreach ($impatientExportSyncEventList as $syncEventList)
			{
				$syncEventMap->addItems($syncEventList);
			}
		}

		return $syncEventMap;
	}

	/**
	 * @param array $sectionIdList
	 * @param int $userId
	 * @param int $connectionId
	 *
	 * @return Sync\Entities\SyncEventMap|null
	 *
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getSyncEventMapBySyncSectionIdCollectionForExport(
		array $sectionIdList,
		int $userId,
		int $connectionId
	): ?Sync\Entities\SyncEventMap
	{
		Loader::includeModule('dav');

		if (!$sectionIdList)
		{
			return null;
		}

		$timestamp = time() - self::TIME_SLICE;
		$eventDb = EventTable::query()
			->setSelect([
	            '*',
	            'EVENT_CONNECTION.*',
	            'EVENT_CONNECTION.CONNECTION',
	            'EVENT_CONNECTION.EVENT',
            ])
			->where('OWNER_ID', $userId)
			->where('CAL_TYPE', Dictionary::EVENT_TYPE['user'])
			->where('DELETED', 'N')
			->where('DATE_TO_TS_UTC', '>', $timestamp)
			// ->whereNot('MEETING_STATUS', 'N')
			->where(Query::filter() // TODO: it's better to optimize it and don't use 'OR' logic here
					 ->logic('or')
					 ->whereNot('MEETING_STATUS', 'N')
					 ->whereNull('MEETING_STATUS')
			)
			->whereIn('SECTION_ID', $sectionIdList)
			->registerRuntimeField('EVENT_CONNECTION',
				new ReferenceField(
					'SYNC_DATA',
					EventConnectionTable::getEntity(),
					Join::on('ref.EVENT_ID', 'this.ID')
						->where('ref.CONNECTION_ID', $connectionId)
					,
					['join_type' => Join::TYPE_LEFT]
				)
			)
			->addOrder('ID')
			->exec()
		;

		$map = new Sync\Entities\SyncEventMap();
		$impatientSyncEventInstanceList = [];

		while ($eventDM = $eventDb->fetchObject())
		{
			$action = Sync\Dictionary::SYNC_EVENT_ACTION['create'];
			$syncEvent = new Sync\Entities\SyncEvent();

			$event = (new EventBuilderFromEntityObject($eventDM))->build();
			$syncEvent->setEvent($event);

			/** @var EO_SectionConnection $sectionConnectionDM */
			if ($eventConnectionDM = $eventDM->get('EVENT_CONNECTION'))
			{
				$eventConnection = (new Sync\Builders\BuilderEventConnectionFromDM($eventConnectionDM))->build();
				$eventConnection->setEvent($event);
				$syncEvent->setEventConnection($eventConnection);

				if (
					in_array($eventConnection->getLastSyncStatus(), Sync\Dictionary::SYNC_EVENT_ACTION, true)
					&& ($eventConnection->getLastSyncStatus() !== Sync\Dictionary::SYNC_EVENT_ACTION['success'])
				)
				{
					$action = $eventConnection->getLastSyncStatus();
				}
				elseif ($event->getVersion() > $eventConnection->getVersion())
				{
					$action = Sync\Dictionary::SYNC_EVENT_ACTION['update'];
				}
				else
				{
					$action = Sync\Dictionary::SYNC_EVENT_ACTION['success'];
				}
			}

			if ($syncEvent->isInstance())
			{
				$syncEvent->setAction($action);
				/** @var SyncEvent $masterEvent */
				$masterEvent = $map->getItem($event->getUid());
				if ($masterEvent)
				{
					if (
						$masterEvent->getAction() === Sync\Dictionary::SYNC_EVENT_ACTION['success']
						&& $syncEvent->getAction() !== Sync\Dictionary::SYNC_EVENT_ACTION['success']
					)
					{
						$masterEvent->setAction(Sync\Dictionary::SYNC_EVENT_ACTION['update']);
					}

					$masterEvent->addInstance($syncEvent);

					continue;
				}

				$impatientSyncEventInstanceList[$event->getUid()][] = $syncEvent;
			}
			else
			{
				if ($instanceList = ($impatientSyncEventInstanceList[$event->getUid()] ?? null))
				{
					$syncEvent->addInstanceList($instanceList);
					unset($impatientSyncEventInstanceList[$event->getUid()]);
					if (
						$syncEvent->getAction() !== Sync\Dictionary::SYNC_EVENT_ACTION['success']
						&& $this->hasCandidatesForUpdate($instanceList)
					)
					{
						$action = Sync\Dictionary::SYNC_EVENT_ACTION['update'];
					}
				}
				$syncEvent->setAction($action);
				$map->add($syncEvent, $event->getUid());
			}
		}

		return $map;
	}

	/**
	 * @param array $list
	 *
	 * @return bool
	 */
	private function hasCandidatesForUpdate(array $list): bool
	{
		return (bool)array_filter($list, function (SyncEvent $syncEvent) {
			return $syncEvent->getAction() !== Sync\Dictionary::SYNC_EVENT_ACTION['success'];
		});
	}
}
