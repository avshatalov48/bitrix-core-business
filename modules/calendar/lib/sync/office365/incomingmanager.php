<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Entities\SyncEventMap;
use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Entities\SyncSectionMap;
use Bitrix\Calendar\Sync\Exceptions\ApiException;
use Bitrix\Calendar\Sync\Exceptions\AuthException;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
use Bitrix\Calendar\Sync\Exceptions\NotFoundException;
use Bitrix\Calendar\Sync\Exceptions\RemoteAccountException;
use Bitrix\Calendar\Sync\Internals\HasContextTrait;
use Bitrix\Calendar\Sync\Managers\IncomingEventManagerInterface;
use Bitrix\Calendar\Sync\Managers\IncomingSectionManagerInterface;
use Bitrix\Calendar\Sync\Office365\Dto\DateTimeDto;
use Bitrix\Calendar\Sync\Office365\Dto\EventDto;
use Bitrix\Calendar\Sync\Office365\Dto\SectionDto;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use DateTime;
use DateTimeZone;
use Exception;

class IncomingManager extends AbstractManager implements IncomingSectionManagerInterface, IncomingEventManagerInterface
{
	use HasContextTrait;

	/**
	 *
	 */
	private const IMPORT_SECTIONS_LIMIT = 10;

	/** @var SectionConnection|null  */
	private ?SectionConnection $lastSectionConnection = null;

	/**
	 * @param Office365Context $context
	 */
	public function __construct(Office365Context $context)
	{
		$this->context = $context;
		parent::__construct($context->getConnection());
	}

	/**
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws BaseException
	 * @throws NotFoundException
	 * @throws RemoteAccountException
	 * @throws LoaderException
	 */
    public function getSections(): Result
    {
		$result = new Result();
		$syncSectionMap = new SyncSectionMap();
		$sections = $this->context->getVendorSyncService()->getSections();
		foreach ($sections as $sectionDto)
		{
			if ($sectionDto->canShare)
			{
				$syncSectionMap->add(
					$this->prepareSyncSection($sectionDto),
					$sectionDto->id
				);
			}
		}

		return $result->setData([
			'externalSyncSectionMap' => $syncSectionMap,
		]);
    }

	/**
	 * @param SectionDto $sectionDto
	 *
	 * @return SyncSection
	 */
	private function prepareSyncSection(SectionDto $sectionDto): SyncSection
	{
		$section = $this->context->getConverter()->convertSection($sectionDto);
		$section
			->setExternalType(Helper::ACCOUNT_TYPE)
			->setOwner($this->connection->getOwner())
			->setCreator($this->connection->getOwner())
			->setIsActive(true)
			->setType('user')
		;
		$sectionConnection = (new SectionConnection())
			->setSection($section)
			->setConnection($this->connection)
			->setVendorSectionId($sectionDto->id)
			->setActive(true)
			->setLastSyncDate(null)
			->setPrimary($sectionDto->isDefaultCalendar ?? false)
			->setOwner($this->connection->getOwner())
			->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
			->setVersionId($sectionDto->changeKey)
		;

		return (new SyncSection())
			->setSection($section)
			->setSectionConnection($sectionConnection)
			->setVendorName($this->getServiceName())
			->setAction(Dictionary::SYNC_STATUS['success'])
			;
	}

	/**
	 * @param SyncSection $syncSection
	 *
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws ConflictException
	 * @throws Core\Base\BaseException
	 * @throws NotFoundException
	 * @throws ObjectException
	 * @throws Exception
	 */
	public function getEvents(SyncSection $syncSection): Result
	{
		$syncEventMap = new SyncEventMap();
		$result = (new Result())->setData([
			'externalSyncEventMap' => $syncEventMap,
		]);
		$service = $this->context->getVendorSyncService();
		$this->lastSectionConnection = $syncSection->getSectionConnection();
		foreach ($service->getCalendarDelta($syncSection->getSectionConnection()) as $eventId => $eventPack)
		{
			/** @var EventDto $dto */
			if ($dto = ($eventPack[Helper::EVENT_TYPES['deleted']] ?? null))
			{
				$this->pushIntoSyncEventMap(
					$syncEventMap,
					$eventId,
					$this->prepareDeletedSyncEvent($dto, $syncSection),
					'delete' // change to dictionary constant
				);
			}
			elseif ($dto = ($eventPack[Helper::EVENT_TYPES['single']] ?? null))
			{
				$this->pushIntoSyncEventMap($syncEventMap, $eventId, $this->prepareSyncEvent($dto, $syncSection));
			}
			elseif ($dto = ($eventPack[Helper::EVENT_TYPES['series']] ?? null))
			{
				$master = $this->prepareSyncEvent($dto, $syncSection);
				$this->pushIntoSyncEventMap($syncEventMap, $eventId, $master);
				if ($master->getEvent()->getRecurringRule())
				{
					$master->getEvent()
						->setExcludedDateCollection(new Core\Event\Properties\ExcludedDatesCollection([]));
					if ($exceptions = ($eventPack[Helper::EVENT_TYPES['exception']] ?? null))
					{
						foreach ($exceptions as $exceptionDto)
						{
							$exception = $this->prepareSyncEvent($exceptionDto, $syncSection)
								->setAction(Dictionary::SYNC_EVENT_ACTION['create'])
							;
							$master->addInstance($exception);
						}
					}
					/** @var DateTimeDto[] $instances */
					$instances = array_map(function (DateTimeDto $val) use ($master) {
						$result = (new DateTime(
							$val->dateTime,
							new DateTimeZone($val->timeZone)
						))->setTimezone($master->getEvent()->getStartTimeZone()->getTimeZone());

						return $result->format('d.m.Y');
					}, ($eventPack[Helper::EVENT_TYPES['occurrence']] ?? []));
					$deltaPeriod = $this->context->getHelper()->getDeltaInterval();
					$computedInstances = (new Core\Event\Tools\Recurrence())->getEventOccurenceDates(
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
								Core\Base\Date::createDateFromFormat($date, 'd.m.Y')
									->resetTime()
									->setDateTimeFormat('d.m.Y')
							);
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param SyncEventMap $map
	 * @param string $key
	 * @param SyncEvent $event
	 * @param string $action
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 */
	private function pushIntoSyncEventMap(
		SyncEventMap $map,
		string $key,
		SyncEvent $event,
		string $action = 'save'
	)
	{
		$event->setAction($action);
		$map->add($event, $key);
	}

	/**
	 * @param EventDto $eventDto
	 * @param SyncSection $section
	 * @return SyncEvent
	 * @throws ObjectException
	 */
	private function prepareSyncEvent(
		EventDto $eventDto,
		SyncSection $section
	): SyncEvent
	{

		$event = $this->context->getConverter()
			->convertEvent($eventDto, $section->getSection());
		$eventConnection = (new EventConnection())
			->setEvent($event)
			->setConnection($section->getSectionConnection()->getConnection())
			->setVendorEventId($eventDto->id)
			->setVendorVersionId($eventDto->changeKey)
			->setEntityTag($eventDto->etag)
			->setRecurrenceId($eventDto->seriesMasterId ?: null)
			->setData($this->prepareCustomData($eventDto))
			->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
		;

		return (new SyncEvent())
			->setEvent($event)
			->setEventConnection($eventConnection);
	}

	/**
	 * @param EventDto $eventDto
	 *
	 * @return array
	 */
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

	/**
	 * @return Result
	 */
	public function getSectionConnection(): Result
	{
		return (new Result())->setData(['sectionConnection' => new SectionConnection()]);
	}

	public function getPageToken(): ?string
	{
		if ($this->lastSectionConnection)
		{
			return $this->lastSectionConnection->getPageToken();
		}
		return null;
	}

	public function getEtag(): ?string
	{
		if ($this->lastSectionConnection)
		{
			return $this->lastSectionConnection->getVersionId();
		}
		return null;
	}

	public function getSyncToken(): ?string
	{
		if ($this->lastSectionConnection)
		{
			return $this->lastSectionConnection->getSyncToken();
		}
		return null;
	}

	public function getStatus(): ?string
	{
		if ($this->lastSectionConnection)
		{
			return $this->lastSectionConnection->getLastSyncStatus();
		}
		return null;
	}

	public function getConnection(): Result
	{
		return (new Result())->setData([
			'connection' => $this->context->getConnection(),
		]);
	}

	/**
	 * @param EventDto $dto
	 * @param SyncSection $syncSection
	 *
	 * @return SyncEvent
	 */
	private function prepareDeletedSyncEvent(EventDto $dto, SyncSection $syncSection): SyncEvent
	{
		$event = new Core\Event\Event();
		$eventConnection = (new EventConnection())
			->setEvent($event)
			->setConnection($syncSection->getSectionConnection()->getConnection())
			->setVendorEventId($dto->id)
		;

		return (new SyncEvent())
			->setEvent($event)
			->setEventConnection($eventConnection);
	}
}
