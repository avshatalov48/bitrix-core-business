<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Processor;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Mappers\Event as EventMapper;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;
use Bitrix\Calendar\Core\Handlers\UpdateMasterExdateHandler;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Entities\InstanceMap;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Handlers\SyncEventMergeHandler;
use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Repository\EventConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\Processor\Event\EventData;
use Bitrix\Calendar\Synchronization\Internal\Service\Processor\Event\LocalEventMap;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;

abstract class AbstractEventImportProcessor
{
	protected LocalEventMap $map;

	public function __construct(
		protected readonly EventConnectionRepository $eventConnectionRepository,
		protected readonly EventMapper $eventMapper,
	)
	{
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function buildLocalEventMap(array $vendorEventsIds, int $connectionId): void
	{
		$eventConnections = $this->eventConnectionRepository->getByIdsAndConnectionId($vendorEventsIds, $connectionId);

		$this->map = new LocalEventMap($eventConnections);
	}

	/**
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws PersistenceException
	 */
	protected function importEventData(
		EventData $externalEventData,
		?EventData $localEventData,
		?EventData $masterEventData
	): void
	{
		$this->processExternalEvent($externalEventData, $masterEventData);

		if (
			$localEventData
			&& (
				$externalEventData->getEvent()->isRecurrence()
				|| $externalEventData->getAction() === Dictionary::SYNC_EVENT_ACTION['delete']
			)
		)
		{
			$this->removeDeprecatedInstances($localEventData, $externalEventData);
		}

		$updatedEventData = new EventData($externalEventData->eventConnection);

		foreach ($externalEventData->getInstances() as $instance)
		{
			$this->processExternalEvent($instance, $updatedEventData);

			$updatedEventData->addInstance(new EventData($instance->eventConnection));
		}

		$this->map->add($externalEventData->eventConnection->getVendorEventId(), $updatedEventData);
	}

	/**
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws PersistenceException
	 */
	protected function processExternalEvent(EventData $externalData, ?EventData $masterEventData = null): void
	{
		if ($masterEventData !== null)
		{
			$externalData->getEvent()->setRecurrenceId($masterEventData->getEvent()->getParentId());
		}

		$this->enrichExternalData($externalData);

		if ($externalData->getAction() === Dictionary::SYNC_EVENT_ACTION['delete'])
		{
			if ($externalData->getEvent()->getId() === null)
			{
				$this->processExternalInstance($externalData, $masterEventData);

				return;
			}

			$this->deleteEvent($externalData->eventConnection);

			return;
		}

		$this->saveEvent($externalData);

		if (
			$masterEventData
			&& $masterEventData->getEvent()->getId()
			&& $externalData->getEvent()->isInstance()
		)
		{
			$this->updateMasterExcludedDates(
				$this->addExcludedDateToMasterEvent($masterEventData->getEvent(), $externalData->getEvent()),
			);
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws PersistenceException
	 */
	private function processExternalInstance(EventData $externalData, ?EventData $masterEventData = null): void
	{
		if (!$externalData->getEvent()->isInstance())
		{
			return;
		}

		if ($masterEventData === null)
		{
			$masterEventData = $this->getMasterLocalEvent(
				$externalData->eventConnection->getRecurrenceId(),
			);
		}

		if (!$masterEventData)
		{
			return;
		}

		if ($masterEventData->getAction() === Dictionary::SYNC_EVENT_ACTION['delete'])
		{
			return;
		}

		if ($masterEventData->getEvent()->getId() === null)
		{
			$this->processExternalEvent($masterEventData);

			return;
		}

		$this->updateMasterExcludedDates(
			$this->addExcludedDateToMasterEvent(
				$masterEventData->getEvent(),
				$externalData->getEvent(),
			),
		);
	}

	/**
	 * @throws BaseException
	 */
	protected function enrichExternalData(EventData $externalData): void
	{
		if ($this->shouldEnrichFromSameLocal($externalData))
		{
			$localEventData = $this->map->get($externalData->eventConnection->getVendorEventId());

			if ($localEventData)
			{
				$this->enrichFromSameLocal($externalData, $localEventData);

				return;
			}
		}

		$localMasterEventData =
			$externalData->eventConnection->getRecurrenceId()
				? $this->map->get($externalData->eventConnection->getRecurrenceId())
				: null
		;

		if ($localMasterEventData)
		{
			if ($localInstance = $localMasterEventData->getSameInstance($externalData))
			{
				$this->mergeEventData(
					$externalData,
					$localInstance,
					$localInstance->getEvent()->getId(),
					$localInstance->eventConnection->getId(),
				);

				return;
			}

			$this->mergeEventData($externalData, $localMasterEventData);
		}
	}

	protected function shouldEnrichFromSameLocal(EventData $externalData): bool
	{
		return $this->map->get($externalData->eventConnection->getVendorEventId()) !== null;
	}

	/**
	 * @throws BaseException
	 */
	protected function enrichFromSameLocal(EventData $externalData, EventData $localEventData): void
	{
		$this->mergeEventData(
			$externalData,
			$localEventData,
			$localEventData->getEvent()->getId(),
			$localEventData->eventConnection->getId(),
		);

		if ($externalData->getEvent()->isRecurrence())
		{
			foreach ($externalData->getInstances() as $instance)
			{
				if ($localInstance = $localEventData->getSameInstance($instance))
				{
					$this->mergeEventData(
						$instance,
						$localInstance,
						$localInstance->getEvent()->getId(),
						$localInstance->eventConnection->getId(),
					);
				}
			}
		}
	}

	/**
	 * @throws BaseException
	 */
	protected function mergeEventData(
		EventData $externalEventData,
		EventData $localEventData,
		int $id = null,
		int $eventConnectionId = null,
	): void
	{
		$existsSyncEvent = new SyncEvent();

		$existsSyncEvent->setEvent($localEventData->getEvent());

		$externalSyncEvent = new SyncEvent();

		$externalSyncEvent->setEvent($externalEventData->getEvent());

		$handler = new SyncEventMergeHandler();
		$handler($existsSyncEvent, $externalSyncEvent, $id);

		$externalEventData->eventConnection
			->setId($eventConnectionId)
			->setVersion($localEventData->getEvent()->getVersion())
			->setRetryCount()
		;
	}

	/**
	 * @throws PersistenceException
	 * @throws ArgumentException
	 */
	private function saveEvent(EventData $externalEvent): void
	{
		$event = $externalEvent->getEvent();

		$event = $event->isNew()
			? $this->eventMapper->create($event, $this->buildSavingParams($externalEvent))
			: $this->eventMapper->update($event, $this->buildSavingParams($externalEvent));

		if ($event)
		{
			$externalEvent->eventConnection
				->setEvent($event)
				->setVersion($event->getVersion())
			;

			$this->eventConnectionRepository->save($externalEvent->eventConnection);
		}
	}

	protected function buildSavingParams(EventData $externalEvent): array
	{
		return [
			'originalFrom' => $externalEvent->eventConnection->getConnection()->getVendor()->getCode(),
		];
	}

	/**
	 * @throws PersistenceException
	 */
	private function deleteEvent(EventConnection $eventConnection): void
	{
		$event = $eventConnection->getEvent();

		// todo handle meeting status
		if ($event->isInstance())
		{
			$this->handleDeleteInstance($eventConnection);
		}

		$this->eventMapper->delete(
			$event,
			[
				'softDelete' => true,
				'originalFrom' => $eventConnection->getConnection()->getVendor()->getCode(),
			],
		);

		$this->map->remove($eventConnection->getVendorEventId());

		$eventConnectionId = $eventConnection->getId();

		if ($eventConnectionId !== null)
		{
			$this->eventConnectionRepository->delete($eventConnectionId);
		}
	}

	private function handleDeleteInstance(EventConnection $eventConnection): void
	{
		$masterLocalEventData = $this->getMasterLocalEvent($eventConnection->getRecurrenceId());

		if (!$masterLocalEventData)
		{
			return;
		}

		$this->prepareExcludedDatesMasterEvent(
			$masterLocalEventData->getEvent(),
			$eventConnection->getEvent()->getOriginalDateFrom(),
		);

		// @todo Can execute?
		if ($masterLocalEventData->getEvent()->getId() === null)
		{
			return;
		}

		$this->updateMasterExcludedDates($masterLocalEventData->getEvent());
		// @todo ???
		$masterLocalEventData->eventConnection->setEvent($masterLocalEventData->getEvent());

		$this->map->add($masterLocalEventData->eventConnection->getVendorEventId(), $masterLocalEventData);
	}

	protected function getMasterLocalEvent(?string $recurringEventId): ?EventData
	{
		return $recurringEventId ? $this->map->get($recurringEventId) : null;
	}

	private function prepareExcludedDatesMasterEvent(Event $masterEvent, Date $excludedDate): void
	{
		$date = clone $excludedDate;
		$date->format(ExcludedDatesCollection::EXCLUDED_DATE_FORMAT);

		// @todo Move to event
		if ($masterEvent->getExcludedDateCollection())
		{
			$masterEvent->getExcludedDateCollection()->add($date);
		}
		else
		{
			$masterEvent->setExcludedDateCollection(new ExcludedDatesCollection([$date]));
		}
	}

	private function updateMasterExcludedDates(Event $masterEvent): void
	{
		$handler = new UpdateMasterExdateHandler();

		$handler($masterEvent);
	}

	protected function addExcludedDateToMasterEvent(Event $masterEvent, Event $externalInstance): Event
	{
		$dates = $masterEvent->getExcludedDateCollection();

		if ($dates === null)
		{
			$dates = new ExcludedDatesCollection();
			$masterEvent->setExcludedDateCollection($dates);
		}

		$originalDate = $externalInstance->getOriginalDateFrom();

		if ($originalDate instanceof Date)
		{
			$dates->add(
				(clone $originalDate)->setDateTimeFormat(ExcludedDatesCollection::EXCLUDED_DATE_FORMAT),
			);
		}

		return $masterEvent;
	}

	/**
	 * @throws PersistenceException
	 */
	protected function removeDeprecatedInstances(EventData $localEventData, EventData $externalEventData): void
	{
		$externalInstances = $externalEventData->getInstances();

		foreach ($localEventData->getInstances() as $oldInstance)
		{
			$key = $this->getInstanceKey($oldInstance, $externalEventData);

			if (empty($externalInstances[$key]))
			{
				// @todo Query in loop!
				$this->eventConnectionRepository->delete($oldInstance->eventConnection->getId());
				$this->eventMapper->delete(
					$oldInstance->getEvent(),
					[
						'softDelete' => false,
						'originalFrom' => $externalEventData->eventConnection->getConnection()->getVendor()->getCode(),
						'recursionMode' => 'this',
					],
				);
			}
		}
	}

	protected function getInstanceKey(EventData $instance, EventData $eventData): ?string
	{
		$instanceEvent = $instance->getEvent();

		return InstanceMap::getKeyByDate($instanceEvent->getOriginalDateFrom() ?: $instanceEvent->getStart());
	}
}
