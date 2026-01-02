<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Processor;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Managers\Compare\EventCompareManager;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Google\Builders\BuilderSyncEventFromExternalData;
use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\EventListResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\EventResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Processor\AbstractEventImportProcessor;
use Bitrix\Calendar\Synchronization\Internal\Service\Processor\Event\EventData;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Processor\Event\ExternalEventData;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;

class EventImportProcessor extends AbstractEventImportProcessor
{
	/**
	 * @param SectionConnection $sectionConnection
	 * @param EventListResponse $eventList
	 *
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws ObjectException
	 * @throws PersistenceException
	 * @throws SystemException
	 */
	public function import(SectionConnection $sectionConnection, EventListResponse $eventList): void
	{
		$ids = $this->getEventConnectionIds($eventList->getItems());

		$this->buildLocalEventMap($ids, $sectionConnection->getConnection()->getId());

		$externalEvents = $this->buildEventsTree($eventList->getItems(), $sectionConnection);

		foreach ($externalEvents as $vendorId => $externalEventData)
		{
			$localEventData = $this->map->get($vendorId);

			if (!$this->shouldEventBeProceed($externalEventData, $localEventData))
			{
				continue;
			}

			$masterEventData = $this->getMasterLocalEvent($externalEventData->eventConnection->getRecurrenceId());
			$masterEvent = $masterEventData?->getEvent();

			if (
				$masterEvent
				&& $externalEventData->getEvent()->isInstance()
				&& $masterEvent->getId() !== $masterEvent->getParentId()
			)
			{
				continue;
			}

			$this->importEventData($externalEventData, $localEventData, $masterEventData);
		}
	}

	/**
	 * @param EventResponse[] $items
	 *
	 * @return string[]
	 */
	private function getEventConnectionIds(array $items): array
	{
		$ids = [];

		foreach ($items as $item)
		{
			$ids[] = $item->id;

			if ($item->isInstance())
			{
				$ids[] = $item->recurringEventId;
			}
		}

		return array_unique($ids);
	}

	/**
	 * @param EventResponse[] $items
	 *
	 * @return ExternalEventData[]
	 *
	 * @throws ObjectException
	 */
	private function buildEventsTree(array $items, SectionConnection $sectionConnection): array
	{
		$syncSection = new SyncSection();

		$syncSection->setSection($sectionConnection->getSection());

		/** @var ExternalEventData[] $tree */
		$tree = [];
		$orphans = [];

		foreach ($items as $item)
		{
			$builder = new BuilderSyncEventFromExternalData(
				$item->toArray(),
				$sectionConnection->getConnection(),
				$syncSection
			);

			$syncEvent = $builder->build();

			$event = $syncEvent->getEvent();

			$eventConnection = new EventConnection();

			$eventConnection
				->setEvent($event)
				->setConnection($sectionConnection->getConnection())
				->setVendorEventId($item->id)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setEntityTag($item->etag)
				->setVersion($item->sequence)
				->setVendorVersionId($item->etag)
				->setRecurrenceId($item->recurringEventId)
				->setData($item->attendees ? ['attendees' => $item->attendees] : [])
			;

			$externalData = new ExternalEventData($item, $eventConnection);

			$id = $item->id;
			$recurringEventId = $item->recurringEventId;

			if ($recurringEventId)
			{
				if (isset($tree[$recurringEventId]))
				{
					$tree[$recurringEventId]->addInstance($externalData);
				}
				else
				{
					$orphans[$recurringEventId][$id] = $externalData;
				}
			}
			else
			{
				$tree[$id] = $externalData;

				if (!empty($item->recurrence) && !empty($orphans[$id]))
				{
					$tree[$id]->setInstances($orphans[$id]);

					unset($orphans[$id]);
				}
			}
		}

		return $this->addOrphansToTree($orphans, $tree);
	}

	/**
	 * @param array $orphans
	 * @param ExternalEventData[] $tree
	 *
	 * @return ExternalEventData[]
	 */
	private function addOrphansToTree(array $orphans, array $tree): array
	{
		foreach ($orphans as $items)
		{
			/** @var ExternalEventData $externalData */
			foreach ($items as $externalData)
			{
				$tree[$externalData->eventConnection->getVendorEventId()] = $externalData;
			}
		}

		return $tree;
	}

	private function shouldEventBeProceed(ExternalEventData $externalData, ?EventData $localData): bool
	{
		if (!$localData)
		{
			return true;
		}

		$externalItem = $externalData->eventConnection;
		$localItem = $localData->eventConnection;

		if (!$localItem->getEvent()->canBeChanged())
		{
			return false;
		}

		if (
			$externalItem->hasIdenticalVersion($localItem)
			&& !$this->hasDifferentEventFields($externalData, $localData)
		)
		{
			return false;
		}

		return $this->shouldEventBeSynchronized($externalData, $localData);
	}

	private function shouldEventBeSynchronized(EventData $externalData, EventData $localData): bool
	{
		$externalItem = $externalData->eventConnection;
		$localItem = $localData->eventConnection;

		if ($externalItem->getEntityTag() !== $localItem->getEntityTag())
		{
			return true;
		}

		if ($externalItem->getEvent()->isInstance())
		{
			return true;
		}

		if ($externalItem->getEvent()->isRecurrence())
		{
			$externalInstances = $externalData->getInstances();

			foreach ($externalInstances as $externalInstance)
			{
				$localInstance = $localData->getSameInstance($externalInstance);

				if (!$localInstance)
				{
					return true;
				}

				if (
					$externalInstance->eventConnection->getEntityTag()
					!== $localInstance->eventConnection->getEntityTag()
				)
				{
					return true;
				}
			}
		}

		return false;
	}

	private function hasDifferentEventFields(EventData $externalData, EventData $localData): bool
	{
		if (!$externalData->getEvent() && !$localData->getEvent())
		{
			return false;
		}

		if ($externalData->isDeleted() && !$localData->getEvent()->isDeleted())
		{
			return true;
		}

		$comparator = new EventCompareManager($externalData->getEvent(), $localData->getEvent());

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

		// recurringRule может отличаться из-за того, что в локальных событиях добавляется until
		// даты начала и конца на целодневных отличаются из-за полей обвески
		// Внешняя:
		// class Bitrix\Calendar\Core\Base\Date#2919 (2) {
		//  private Bitrix\Main\Type\Date $date =>
		//  class Bitrix\Main\Type\Date#3178 (1) {
		//    protected $value =>
		//    class DateTime#2810 (3) {
		//      public $date =>
		//      string(26) "1989-01-06 00:00:00.000000"
		//      public $timezone_type =>
		//      int(3)
		//      public $timezone =>
		//      string(3) "UTC"
		//    }
		//  }
		//  private $format =>
		//  string(5) "d.m.Y"
		//}
		// Внутренняя:
		// class Bitrix\Calendar\Core\Base\Date#1215 (2) {
		//  private Bitrix\Main\Type\Date $date =>
		//  class Bitrix\Main\Type\DateTime#1214 (2) {
		//    protected $value =>
		//    class DateTime#1212 (3) {
		//      public $date =>
		//      string(26) "1989-01-06 00:00:00.000000"
		//      public $timezone_type =>
		//      int(3)
		//      public $timezone =>
		//      string(3) "UTC"
		//    }
		//    protected $userTimeEnabled =>
		//    bool(true)
		//  }
		//  private $format =>
		//  string(11) "d.m.Y H:i:s"
		//}

		return !empty($significantDiff);
	}
}
