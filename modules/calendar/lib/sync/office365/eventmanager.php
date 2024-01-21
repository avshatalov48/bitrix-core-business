<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Bizproc\Error;
use Bitrix\Calendar;
use Bitrix\Calendar\Sync\Exceptions\GoneException;
use Bitrix\Main;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Exceptions\ApiException;
use Bitrix\Calendar\Sync\Exceptions\AuthException;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
use Bitrix\Calendar\Sync\Exceptions\NotFoundException;
use Bitrix\Calendar\Sync\Exceptions\RemoteAccountException;
use Bitrix\Calendar\Sync\Internals\ContextInterface;
use Bitrix\Calendar\Sync\Internals\HasContextTrait;
use Bitrix\Calendar\Sync\Managers\EventManagerInterface;
use Bitrix\Calendar\Sync\Util\EventContext;
use Bitrix\Calendar\Sync\Office365\Converter\EventConverter;
use Bitrix\Calendar\Sync\Office365\Dto\EventDto;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Sync\Office365;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use DateTimeZone;
use Generator;

class EventManager extends AbstractManager implements EventManagerInterface
{
	use HasContextTrait;

	private Helper $helper;

	/**
	 * @var ?EventConverter
	 */
	private ?EventConverter $eventConverter;

	private Core\Mappers\EventConnection $eventConnectionMapper;

	/**
	 * @param Office365\Office365Context $context
	 */
	public function __construct(ContextInterface $context)
	{
		$this->context = $context;
		$this->helper = $context->getHelper();
		parent::__construct($context->getConnection());
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @throws Main\ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws Calendar\Sync\Exceptions\NotFoundException
	 * @throws LoaderException
	 */
	public function create(Core\Event\Event $event, EventContext $context): Result
	{
		$result = new Result();
		$internalDto = $this->getEventConverter()->eventToDto($event);

		try
		{
			$dto = $this->getService()->createEvent($internalDto, $context->getSectionConnection()->getVendorSectionId());
			if ($dto)
			{
				if ($event->getExcludedDateCollection() && $event->getExcludedDateCollection()->count())
				{
					$context->add('sync', 'masterEventId', $dto->id);
					/** @var Main\Type\DateTime $item */
					foreach ($event->getExcludedDateCollection() as $item)
					{
						$context->add('sync', 'excludeDate', $item);
						$this->deleteInstance($event, $context);
					}
				}

				if (!empty($dto->id))
				{
					$result->setData($this->prepareResultData($dto));
				}
				else
				{
					$result->addError(new Main\Error('Error of create a series master event'));
				}
			}
			else
			{
				$result->addError(new Main\Error('Error of create event'));
			}
		}
		catch (ApiException $exception)
		{
			if ((int)$exception->getCode() !== 400)
			{
				throw $exception;
			}

			$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
		}
		catch (AuthException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @throws Main\ArgumentException
	 * @throws Calendar\Sync\Exceptions\ApiException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws LoaderException
	 */
	public function update(Core\Event\Event $event, EventContext $context): Result
	{
		$result = new Result();
		$internalDto = $this->getEventConverter()->eventToDto($event);
		$this->enrichVendorData($internalDto, $context->getEventConnection());

		try
		{
			$dto = $this->getService()->updateEvent($context->getEventConnection()->getVendorEventId(), $internalDto);
			if ($dto)
			{
				$result->setData($this->prepareResultData($dto));
			}
		}
		catch (ApiException $exception)
		{
			if ((int)$exception->getCode() !== 400)
			{
				throw $exception;
			}

			$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
		}
		catch (AuthException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws ApiException
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws AuthException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws NotFoundException
	 * @throws RemoteAccountException
	 * @throws LoaderException
	 */
	public function delete(Core\Event\Event $event, EventContext $context): Result
	{
		$this->getService()->deleteEvent($context->getEventConnection()->getVendorEventId());

		return new Result();
	}

	/**
	 * @param EventDto $dto
	 *
	 * @return array
	 */
	private function prepareResultData(EventDto $dto): array
	{
		return [
			'dto' => $dto,
			'event' => [
				'id' => $dto->id,
				'version' => $dto->changeKey,
				'etag' => $dto->etag,
				'recurrence' => $dto->seriesMasterId ?? null,
				'data' => $this->prepareCustomData($dto),
			],
		];
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Calendar\Sync\Exceptions\ApiException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws LoaderException
	 */
	public function updateInstance(Event $event, EventContext $context): Result
	{
		$eventLink = $context->sync['instanceLink'];
		if ($eventLink)
		{
			$eventContext = (new EventContext())
				->setSectionConnection($context->getSectionConnection())
				->setEventConnection($eventLink)
			;
			return $this->update($event, $eventContext);
		}

		return (new Result())->addError(new Main\Error('Not found link for instance'));
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @throws Main\ArgumentException
	 * @throws Calendar\Sync\Exceptions\ApiException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws LoaderException
	 */
	public function createInstance(Core\Event\Event $event, EventContext $context): Result
	{
		if (
			$event->getOriginalDateFrom()
			&& $event->getOriginalDateFrom()->format('Ymd') !== $event->getStart()->format('Ymd')
		)
		{
			return $this->moveInstance($event, $context);
		}

		$result = new Result();
		$masterLink = $context->getEventConnection();

		try
		{
			if ($masterLink && $instance = $this->getInstanceForDay($masterLink->getVendorEventId(), $event->getStart()->getDate()))
			{
				$dto = $this->getService()->updateEvent(
					$instance->id,
					$this->getEventConverter()->eventToDto($event),
				);
				if ($dto && !empty($dto->id))
				{
					$result->setData($this->prepareResultData($dto));
				}
				else
				{
					$result->addError(new Main\Error("Error of create instance.", 404));
				}
			}
			else
			{
				$result->addError(new Main\Error("Instances for event not found", 404));
			}
		}
		catch (ApiException $exception)
		{
			if (!in_array((int)$exception->getCode(), [400, 404], true))
			{
				throw $exception;
			}

			$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
		}
		catch (AuthException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws AuthException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws NotFoundException
	 * @throws Main\ObjectException
	 * @throws RemoteAccountException
	 * @throws LoaderException
	 */
	public function deleteInstance(Event $event, EventContext $context): Result
	{
		$result = new Result();
		$masterEventId = $context->getEventConnection()
			? $context->getEventConnection()->getVendorEventId()
			: ($context->sync['masterEventId'] ?? null)
		;
		if ($masterEventId)
		{
			$excludeDate = new Main\Type\DateTime(
				$context->sync['excludeDate']->getDate()->format('Ymd 000000'),
				'Ymd His',
				$event->getStartTimeZone()
					? $event->getStartTimeZone()->getTimeZone()
					: new \DateTimeZone('UTC')
			);
			try
			{
				if ($instance = $this->getInstanceForDay($masterEventId, $excludeDate))
				{
					$this->getService()->deleteEvent(
						$instance->id,
					);
				}
				else
				{
					$result->addError(new Main\Error("Instances for event not found", 404));
				}
			}
			catch (ApiException $e)
			{
				if ((int)$e->getCode() !== 400 && (int)$e->getCode() !== 404)
				{
					throw $e;
				}

				$result->addError(new Main\Error($e->getMessage(), $e->getCode()));
			}
			catch (AuthException $exception)
			{
				$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
			}
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws AuthException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws NotFoundException
	 * @throws RemoteAccountException
	 * @throws LoaderException
	 */
	private function moveInstance(Event $event, EventContext $context): Result
	{
		$result = new Result();
		$instance = null;
		$masterLink = $context->getEventConnection();

		if ($masterLink && $event->getOriginalDateFrom())
		{
			$instance = $this->getInstanceForDay(
				$masterLink->getVendorEventId(),
				$event->getOriginalDateFrom()->getDate()
			);
		}

		if ($instance)
		{
			try
			{
				$dto = $this->getService()->updateEvent(
					$instance->id,
					$this->getEventConverter()->eventToDto($event),
				);
				if ($dto && !empty($dto->id))
				{
					$result->setData($this->prepareResultData($dto));
				}
				else
				{
					$result->addError(new Main\Error('Error of move instance', 400));
				}
			}
			catch (NotFoundException $e)
			{
				$result->addError(new Main\Error('Instance not found'));
			}
		}
		else
		{
			$result->addError(new Main\Error('Instance not found'));
		}

		return $result;
	}

	/**
	 * @param SectionConnection $sectionLink
	 *
	 * @return Generator|array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws AuthException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws LoaderException
	 * @throws ObjectException
	 * @throws NotFoundException
	 * @throws RemoteAccountException
	 * @deprecated use Sync\Office365\IncomingManager::getEvents()
	 */
	public function fetchSectionEvents(SectionConnection $sectionLink): Generator
	{
		foreach ($this->getService()->getCalendarDelta($sectionLink) as $deltaData)
		{
			$data = [];
			if (!empty($deltaData[Helper::EVENT_TYPES['deleted']]))
			{
				/** @var EventDto $dto */
				$dto = $deltaData[Helper::EVENT_TYPES['deleted']];
				$data[] = [
					'type' => 'deleted',
					'id' => $dto->id,
					'version' => $dto->changeKey,
					'etag' => $dto->etag,
				];


//				$this->processEventInstance($deltaData[Helper::EVENT_TYPES['deleted']], $sectionLink);
			}
			elseif (!empty($deltaData[Helper::EVENT_TYPES['single']]))
			{
				/** @var EventDto $dto */
				$dto = $deltaData[Helper::EVENT_TYPES['single']];
				$data[] = [
					'type' => 'single',
					'event' => $this->context->getConverter()
						->convertEvent($dto, $sectionLink->getSection()),
					'id' => $dto->id,
					'version' => $dto->changeKey,
					'etag' => $dto->etag,
					'data' => $this->prepareCustomData($dto),
				];
			}
			elseif (!empty($deltaData[Helper::EVENT_TYPES['series']]))
			{
				$data = $this->prepareSeries($deltaData, $sectionLink);

			}

			if ($data)
			{
				yield $data;
			}
		}
	}

	/**
	 * @param SyncEvent $recurrenceEvent
	 * @param SectionConnection $sectionConnection
	 * @param Context $context
	 *
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws AuthException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws NotFoundException
	 * @throws ObjectException
	 * @throws RemoteAccountException
	 */
	public function saveRecurrence(
		SyncEvent $recurrenceEvent,
		SectionConnection $sectionConnection,
		Context $context
	): Result
	{
		$result = new Result();

		if ($recurrenceEvent->getEventConnection())
		{
			try
			{
				$masterResult = $this->updateRecurrenceInstance($recurrenceEvent, $context);
			}
			catch(Calendar\Sync\Exceptions\NotFoundException $e)
			{
				$this->getEventConnectionMapper()->delete($recurrenceEvent->getEventConnection());
				$recurrenceEvent->setEventConnection(null);
				return $this->saveRecurrence($recurrenceEvent, $sectionConnection, $context);
			}
		}
		else
		{
			$masterResult = $this->createRecurrenceInstance($recurrenceEvent, $sectionConnection, $context);
		}

		if (!$masterResult->isSuccess())
		{
			$result->addErrors($masterResult->getErrors());
			return $result;
		}

		if ($recurrenceEvent->getEventConnection())
		{
			$recurrenceEvent->getEventConnection()
				->setLastSyncStatus(Calendar\Sync\Dictionary::SYNC_STATUS['success']);
		}
		$recurrenceEvent
			->setAction(Calendar\Sync\Dictionary::SYNC_STATUS['success']);

		$excludes = $this->getExcludedDatesCollection($recurrenceEvent);
		if ($recurrenceEvent->getInstanceMap())
		{
			/** @var SyncEvent $instance */
			foreach ($recurrenceEvent->getInstanceMap()->getCollection() as $instance)
			{
				if ($instance->getEvent()->getOriginalDateFrom() === null)
				{
					$result->addError(
						new Main\Error('Instance is invalid - there is not original date from. ['.$instance->getEvent()->getId().']', 400));
					continue;
				}

				if ($instance->getEventConnection())
				{
					try
					{
						$instanceResult = $this->updateRecurrenceInstance($instance, $context, $recurrenceEvent->getEventConnection());
					}
					catch(Calendar\Sync\Exceptions\NotFoundException $e)
					{
						$this->getEventConnectionMapper()->delete($instance->getEventConnection());
						$instance->setEventConnection(null);
						$instanceResult = $this->createRecurrenceInstance(
							$instance,
							$sectionConnection,
							$context,
							$recurrenceEvent->getEventConnection()
						);
					}
				}
				else
				{
					$instanceResult = $this->createRecurrenceInstance(
						$instance,
						$sectionConnection,
						$context,
						$recurrenceEvent->getEventConnection()
					);
				}
				$excludes->removeDateFromCollection($instance->getEvent()->getOriginalDateFrom());
				if (
					$instance->getEvent()->getStart()->format('Ymd')
					!== $instance->getEvent()->getOriginalDateFrom()->format('Ymd')
				)
				{
					$excludes->removeDateFromCollection($instance->getEvent()->getStart());
				}
				if (!$instanceResult->isSuccess())
				{
					$result->addErrors($instanceResult->getErrors());
				}
				if ($instance->getEventConnection())
				{
					$instance->getEventConnection()->setLastSyncStatus(Calendar\Sync\Dictionary::SYNC_STATUS['success']);
				}
			}
		}

		if ($excludes->count() > 0)
		{
			$context = (new EventContext())->setEventConnection($recurrenceEvent->getEventConnection());
			/** @var Main\Type\Date $excludedDate */
			foreach ($excludes as $excludedDate)
			{
				$context->add('sync', 'excludeDate', $excludedDate);
				$this->deleteInstance($recurrenceEvent->getEvent(), $context);
			}
		}

		return $result;
	}

	/**
	 * @return Core\Mappers\EventConnection
	 */
	private function getEventConnectionMapper(): Core\Mappers\EventConnection
	{
		if (empty($this->eventConnectionMapper))
		{
			$this->eventConnectionMapper = new Core\Mappers\EventConnection();
		}

		return $this->eventConnectionMapper;
	}

	/**
	 * @param SyncEvent $recurrenceEvent
	 * @param SectionConnection $sectionConnection
	 * @param Context $context
	 *
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws AuthException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws LoaderException
	 * @throws NotFoundException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws RemoteAccountException
	 * @throws SystemException
	 */
	public function createRecurrence(
		SyncEvent $recurrenceEvent,
		SectionConnection $sectionConnection,
		Context $context
	): Result
	{
		return $this->saveRecurrence($recurrenceEvent, $sectionConnection, $context);
	}

	/**
	 * @param SyncEvent $recurrenceEvent
	 * @param SectionConnection $sectionConnection
	 * @param Context $context
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws LoaderException
	 */
	public function updateRecurrence(
		SyncEvent $recurrenceEvent,
		SectionConnection $sectionConnection,
		Context $context
	): Result
	{
		return $this->saveRecurrence($recurrenceEvent, $sectionConnection, $context);
	}

	/**
	 * @param $deltaData
	 * @param SectionConnection $sectionLink
	 *
	 * @return void
	 *
	 * @throws Main\ObjectException
	 */
	private function prepareSeries($deltaData, SectionConnection $sectionLink): array
	{
		$result = [];
		/** @var EventDto $masterDto */
		$masterDto    = $deltaData[Helper::EVENT_TYPES['series']];
		/** @var EventDto[] $exceptionList */
		$exceptionList = $deltaData[Helper::EVENT_TYPES['exception']] ?? [];

		$masterEvent = $this->context->getConverter()->convertEvent($masterDto, $sectionLink->getSection());

		if (!empty($exceptionList)) {
			$exceptionsDates = array_map(function (EventDto $exception) {
				return (new Main\Type\DateTime(
					$exception->start->dateTime,
					$this->helper::TIME_FORMAT_LONG,
					new DateTimeZone($exception->start->timeZone),
					))->format('d.m.Y');
				}, $exceptionList
			);

			$excludeCollection = new Core\Event\Properties\ExcludedDatesCollection($exceptionsDates);
			$masterEvent->setExcludedDateCollection($excludeCollection);
		}

		$result[] = [
			'type' => 'master',
			'id' => $masterDto->id,
			'event' => $masterEvent,
			'version' => $masterDto->changeKey,
			'etag' => $masterDto->etag,
			'data' => $this->prepareCustomData($masterDto),
		];

		foreach ($exceptionList as $exception) {
			$event = $this->context->getConverter()->convertEvent($exception, $sectionLink->getSection());
			$result[] = [
				'type' => 'exception',
				'event' => $event,
				'id' => $exception->id,
				'version' => $exception->changeKey,
				'etag' => $exception->etag,
				'recurrence' => $exception->seriesMasterId,
				'data' => $this->prepareCustomData($exception),
			];
		}

		return $result;
	}

	/**
	 * @param string $eventId
	 * @param Date $dayStart
	 *
	 * @return EventDto|null
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws BaseException
	 * @throws GoneException
	 * @throws ConflictException
	 * @throws LoaderException
	 * @throws NotFoundException
	 * @throws RemoteAccountException
	 */
	private function getInstanceForDay(string $eventId, Main\Type\Date $dayStart): ? EventDto
	{
		$dateFrom = clone $dayStart;
		if ($dateFrom instanceof Main\Type\DateTime)
		{
			$dateFrom->setTime(0,0);
		}
		$dateTo = clone $dateFrom;
		$dateTo->add('1 day');

		$instances = $this->getService()->getEventInstances([
			'filter' => [
				'event_id' => $eventId,
				'from' => $dateFrom->format('c'), //($this->helper::TIME_FORMAT_LONG),
				'to' => $dateTo->format('c'), //($this->helper::TIME_FORMAT_LONG),
			],
		]);

		return $instances ? $instances[0] : null;
	}

	/**
	 * @return EventConverter
	 */
	private function getEventConverter(): EventConverter
	{
		if (empty($this->eventConverter))
		{
			$this->eventConverter = new EventConverter();
		}

		return $this->eventConverter;
	}

	/**
	 * @return VendorSyncService
	 *
	 * @throws AuthException
	 * @throws BaseException
	 * @throws LoaderException
	 * @throws RemoteAccountException
	 */
	private function getService(): VendorSyncService
	{
		return $this->context->getVendorSyncService();
	}

	/**
	 * @param EventDto $dto
	 *
	 * @return array
	 * @todo see Sync\Office365\IncomingManager::prepareCustomData()
	 */
	private function prepareCustomData(EventDto $dto): array
	{
		$result = [];
		if (!empty($dto->location))
		{
			$result['location'] = $dto->location->toArray(true);
		}
		if (!empty($dto->locations))
		{
			foreach ($dto->locations as $location)
			{
				$result['locations'][] = $location->toArray(true);
			}
		}

		if (!empty($dto->attendees))
		{
			$result['attendees'] = [];
			foreach ($dto->attendees as $attendee)
			{
				$result['attendees'][] = $attendee->toArray(true);
			}
		}

		return $result;
	}

	/**
	 * @param EventDto $dto
	 * @param EventConnection $link
	 *
	 * @return void
	 */
	private function enrichVendorData(EventDto $dto, EventConnection $link)
	{
		// TODO: add info about attendees, locations and any others
	}

	/**
	 * @param SyncEvent $syncEvent
	 * @param SectionConnection $sectionConnection
	 * @param Context $context
	 * @param EventConnection|null $masterLink
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function createRecurrenceInstance(
		SyncEvent $syncEvent,
		SectionConnection $sectionConnection,
		Context $context,
		EventConnection $masterLink = null
	): Result
	{
		try
		{
			$eventContext = new EventContext();
			$eventContext->merge($context);
			if ($masterLink)
			{
				$eventContext->setEventConnection($masterLink);
				$event = (new Core\Builders\EventCloner($syncEvent->getEvent()))->build();
				$result = $this->createInstance($event, $eventContext);
			}
			else
			{
				$eventContext->setSectionConnection($sectionConnection);
				$event = $this->prepareMasterEvent($syncEvent);
				$result = $this->create($event, $eventContext);
			}
			if ($result->isSuccess())
			{
				if (!$syncEvent->getEvent()->isDeleted())
				{
					if (!empty($result->getData()['event']['id']))
					{
						$link = (new EventConnection())
							->setEvent($event)
							->setConnection($sectionConnection->getConnection())
							->setVersion($event->getVersion())
							->setVendorEventId($result->getData()['event']['id'])
							->setEntityTag($result->getData()['event']['etag'])
							->setVendorVersionId($result->getData()['event']['version'])
							->setRecurrenceId($result->getData()['event']['recurrence'] ?? null)
							->setLastSyncStatus(Calendar\Sync\Dictionary::SYNC_STATUS['success'])
						;
						$syncEvent
							->setEventConnection($link)
							->setAction(Calendar\Sync\Dictionary::SYNC_STATUS['success']);
					}
					else
					{
						$errMessage = 'Unknown error of creating recurrence '
							. ($masterLink ? 'master' : 'instance');
						$result->addError(new Main\Error($errMessage, 400, ['data' => $result->getData()]));
					}

				}
				else
				{
					$syncEvent->setAction(Calendar\Sync\Dictionary::SYNC_EVENT_ACTION['delete']);
				}
			}

			return $result;
		}
		catch (Core\Base\BaseException $e)
		{
		    return (new Result())->addError(new Main\Error($e->getMessage()));
		}
	}

	/**
	 * @param SyncEvent $syncEvent
	 * @param Context $context
	 * @param EventConnection|null $masterLink
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws Calendar\Sync\Exceptions\ApiException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws LoaderException
	 */
	private function updateRecurrenceInstance(
		SyncEvent $syncEvent,
		Context $context,
		EventConnection $masterLink = null
	): Result
	{
		$eventContext = new EventContext();
		$eventContext->merge($context);
		if ($masterLink)
		{
			$eventContext
				->setEventConnection($masterLink)
				->add('sync', 'instanceLink', $syncEvent->getEventConnection())
			;
			$result = $this->updateInstance($syncEvent->getEvent(), $eventContext);
		}
		else
		{
			$eventContext->setEventConnection($syncEvent->getEventConnection());
			$result = $this->update($syncEvent->getEvent(), $eventContext);
		}
		if ($result->isSuccess())
		{
			$syncEvent->getEventConnection()
				->setEntityTag($result->getData()['event']['etag'])
				->setVendorVersionId($result->getData()['event']['version'])
			;
		}

		return $result;
	}

	/**
	 * @param SyncEvent $recurrenceEvent
	 *
	 * @return Core\Event\Properties\ExcludedDatesCollection
	 */
	private function getExcludedDatesCollection(SyncEvent $recurrenceEvent): Core\Event\Properties\ExcludedDatesCollection
	{
		$excludes = new Core\Event\Properties\ExcludedDatesCollection();

		/** @var Core\Base\Date $item */
		foreach ($recurrenceEvent->getEvent()->getExcludedDateCollection() as $item)
		{
			$excludes->add($item);
		}
		return $excludes;
	}

	/**
	 * @param SyncEvent $syncEvent
	 *
	 * @return Event
	 */
	private function prepareMasterEvent(SyncEvent $syncEvent): Event
	{
		$event = (new Core\Builders\EventCloner($syncEvent->getEvent()))->build();
		(new Calendar\Sync\Util\ExcludeDatesHandler())->prepareEventExcludeDates($event, $syncEvent->getInstanceMap());

		return $event;
	}
}
