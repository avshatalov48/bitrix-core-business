<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Processor;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;
use Bitrix\Calendar\Core\Event\Tools\Recurrence;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Office365\Dto\DateTimeDto;
use Bitrix\Calendar\Sync\Office365\Dto\EventDto;
use Bitrix\Calendar\Sync\Office365\Helper;
use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoConvertException;
use Bitrix\Calendar\Synchronization\Internal\Repository\EventConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\Processor\AbstractEventImportProcessor;
use Bitrix\Calendar\Synchronization\Internal\Service\Processor\Event\EventData;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\EventListResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Processor\Event\DtoConverter;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Processor\Event\ExternalEventData;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Util\DeltaIntervalGenerator;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;
use Psr\Container\NotFoundExceptionInterface;

class EventImportProcessor extends AbstractEventImportProcessor
{
	public function __construct(
		EventConnectionRepository $eventConnectionRepository,
		\Bitrix\Calendar\Core\Mappers\Event $eventMapper,
		private readonly DtoConverter $dtoConverter
	)
	{
		parent::__construct($eventConnectionRepository, $eventMapper);
	}

	/**
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws NotFoundExceptionInterface
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws PersistenceException
	 * @throws SystemException
	 * @throws ObjectPropertyException
	 * @throws \DateInvalidTimeZoneException
	 * @throws \DateMalformedStringException
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
				$masterEventData
				&& $externalEventData->getEvent()->isInstance()
				&& $masterEvent->getId() !== $masterEvent->getParentId()
			)
			{
				continue;
			}

			$this->importEventData($externalEventData, $localEventData, $masterEventData);
		}
	}

	protected function getInstanceKey(EventData $instance, EventData $eventData): ?string
	{
		return $eventData->getInstanceKeyFromEventConnection($instance->eventConnection);
	}

	/**
	 * @return string[]
	 */
	private function getEventConnectionIds(array $items): array
	{
		$ids = [];

		foreach ($items as $id => $item)
		{
			$ids[] = $id;

			if (isset($item['exception']))
			{
				$ids = array_merge($ids, array_keys($item['exception']));
			}
		}

		return $ids;
	}

	/**
	 * @return ExternalEventData[]
	 *
	 * @throws BaseException
	 * @throws NotFoundExceptionInterface
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws SystemException
	 * @throws \DateInvalidTimeZoneException
	 * @throws \DateMalformedStringException
	 */
	private function buildEventsTree(array $items, SectionConnection $sectionConnection): array
	{
		$tree = [];

		foreach ($items as $eventId => $eventPack)
		{
			/** @var EventDto $dto */
			if ($dto = ($eventPack[Helper::EVENT_TYPES['deleted']] ?? null))
			{
				$tree[$eventId] = $this->prepareDeletedSyncEvent($dto, $sectionConnection->getConnection());
			}
			elseif ($dto = ($eventPack[Helper::EVENT_TYPES['single']] ?? null))
			{
				$tree[$eventId] = $this->prepareSyncEvent($dto, $sectionConnection);
			}
			elseif ($dto = ($eventPack[Helper::EVENT_TYPES['series']] ?? null))
			{
				$tree[$eventId] = $master = $this->prepareSyncEvent($dto, $sectionConnection);

				if ($master->getEvent()->getRecurringRule())
				{
					$master
						->getEvent()
						->setExcludedDateCollection(new ExcludedDatesCollection([]))
					;

					if ($exceptions = ($eventPack[Helper::EVENT_TYPES['exception']] ?? null))
					{
						foreach ($exceptions as $exceptionDto)
						{
							$exception = $this->prepareSyncEvent(
								$exceptionDto, $sectionConnection, Dictionary::SYNC_EVENT_ACTION['create']
							);

							$master->addInstance($exception);
						}
					}

					/** @var DateTimeDto[] $instances */
					$instances = array_map(
						function (DateTimeDto $val) use ($master) {
							$result = (new \DateTime($val->dateTime, new \DateTimeZone($val->timeZone)))
								->setTimezone($master->getEvent()->getStartTimeZone()->getTimeZone())
							;

							return $result->format('d.m.Y');
						},
						($eventPack[Helper::EVENT_TYPES['occurrence']] ?? [])
					);

					$deltaPeriod = ServiceLocator::getInstance()->get(DeltaIntervalGenerator::class)->getDeltaInterval(
					);

					$computedInstances = (new Recurrence())->getEventOccurenceDates(
						$master->getEvent(),
						[
							'limitDateFrom' => $deltaPeriod['from'],
							'limitDateTo' => $deltaPeriod['to'],
						]
					);

					foreach ($computedInstances as $date => $dateTime)
					{
						if (!in_array($date, $instances))
						{
							$master->getEvent()->getExcludedDateCollection()->add(
								Date::createDateFromFormat($date, 'd.m.Y')
									->resetTime()
									->setDateTimeFormat('d.m.Y')
							);
						}
					}
				}
			}
		}

		return $tree;
	}

	private function prepareDeletedSyncEvent(EventDto $dto, Connection $connection): ExternalEventData
	{
		$eventConnection = (new EventConnection())
			->setEvent(new Event())
			->setConnection($connection)
			->setVendorEventId($dto->id)
		;

		return new ExternalEventData(
			$eventConnection,
			'delete'
		);
	}

	/**
	 * @throws DtoConvertException
	 */
	private function prepareSyncEvent(
		EventDto $eventDto,
		SectionConnection $sectionConnection,
		string $action = 'save'
	): ExternalEventData
	{
		$event = $this->dtoConverter->convertEvent($eventDto, $sectionConnection);

		$eventConnection = (new EventConnection())
			->setEvent($event)
			->setConnection($sectionConnection->getConnection())
			->setVendorEventId($eventDto->id)
			->setVendorVersionId($eventDto->changeKey)
			->setEntityTag($eventDto->etag)
			->setRecurrenceId($eventDto->seriesMasterId ?: null)
			->setData($this->prepareCustomData($eventDto))
			->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
		;

		return new ExternalEventData($eventConnection, $action);
	}

	private function prepareCustomData(EventDto $eventDto): array
	{
		$data = [];
		if (!empty($eventDto->location))
		{
			$data['location'] = $eventDto->location->toArray(true);
		}
		if (!empty($eventDto->locations))
		{
			foreach ($eventDto->locations as $location)
			{
				$data['locations'][] = $location->toArray(true);
			}
		}

		if (!empty($eventDto->attendees))
		{
			$data['attendees'] = [];
			foreach ($eventDto->attendees as $attendee)
			{
				$data['attendees'][] = $attendee->toArray(true);
			}
		}

		return $data;
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

	private function shouldEventBeSynchronized(ExternalEventData $externalData, EventData $localData): bool
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

	private function hasDifferentEventFields(ExternalEventData $externalData, EventData $localData): bool
	{
		return false;
	}
}
