<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Connection\Server;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
use Bitrix\Calendar\Sync\Managers\EventManagerInterface;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Sync\Util\EventContext;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Main\Web\Json;

class EventManager extends Manager implements EventManagerInterface
{
	public const CREATE_PATH = '/calendars/%CALENDAR_ID%/events/';
	public const EVENT_PATH = '/calendars/%CALENDAR_ID%/events/%EVENT_ID%';

	/**
	 * @param Core\Event\Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @throws LoaderException
	 */
	public function create(Core\Event\Event $event, EventContext $context): Result
	{
		$result = new Result();

		try
		{
			// TODO: Remake it: move this logic to parent::request().
			// Or, better, in separate class.
			$this->httpClient->post(
				$this->prepareCreateUrl($context),
				$this->encode((new EventConverter($event))->convertForCreate())
			);

			if ($this->isRequestSuccess())
			{
				$requestResult = $this->prepareResult($this->httpClient->getResult(), $event);

				$result->setData($requestResult);
			}
			else
			{
				$response = Json::decode($this->httpClient->getResult());
				if (!empty($response['error']))
				{
					$error = $response['error'];
					switch ($error['code'])
					{
						case 409:
							throw new ConflictException($error['message'], $error['code']);
						case 401:
							$this->handleUnauthorize($this->connection);
							$result->addError(new Error($error['message'], $error['code']));
							break;
						default:
							if (!empty($error['code']))
							{
								$result->addError(new Error($error['message'], $error['code']));
							}
							else
							{
								$result->addError(new Error('Uncknown Google API error', 400));
							}
					}
				}
			}
		}
		catch (ConflictException $e)
		{
			if ($event->getUid())
			{
				$event->setUid(null);
				return $this->create($event, $context);
			}
			else
			{
				$result->addError(new Error($e->getMessage(), $e->getCode()));
			}
		}
		catch (ArgumentException $e)
		{
			AddMessage2Log($e->getMessage(), 'calendar', 2, true);
			$result->addError(new Error('failed to create an event in google'));
		}
		catch (ObjectException $e)
		{
			AddMessage2Log($e->getMessage(), 'calendar', 2, true);
			$result->addError(new Error('failed to convert event'));
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @throws BaseException
	 * @throws LoaderException
	 * @throws SystemException
	 */
	public function update(Core\Event\Event $event, EventContext $context): Result
	{
		$result = new Result();

		try
		{
			// TODO: Remake it: move this logic to parent::request().
			// Or, better, in separate class.
			$this->httpClient->query(
				HttpClient::HTTP_PUT,
				$this->prepareUpdateUrl($context),
				$this->encode((new EventConverter($event, $context->getEventConnection()))->convertForUpdate())
			);

			if ($this->isRequestSuccess())
			{
				$requestResult = $this->prepareResult($this->httpClient->getResult(), $event);
				// TODO: move it to core
				if ($childList = ($context->sync['childList'] ?? null))
				{
					/**
					 * @var int $eventId
					 * @var Event $childEvent
					 */
					foreach ($childList as $eventId => $childEvent)
					{
						$childResult = $this->createInstance($childEvent, $context);
						if ($childResult->isSuccess())
						{
							$data = $childResult->getData()['event'];
							$mapper = new Core\Mappers\EventConnection();
							/** @var EventConnection $link */
							$link = $mapper->getMap([
								'=EVENT_ID' => $childEvent->getId(),
								'=CONNECTION_ID' => $this->connection->getId(),
							])->fetch();
							if ($link)
							{
								$link
									->setVendorEventId($data['id'])
									->setEntityTag($data['etag'])
									->setVendorVersionId($data['version'])
									->setRecurrenceId($requestResult['event']['id'])
									;
								$mapper->update($link);
							}
						}
					}
				}
				$result->setData($requestResult);
			}
			else
			{
				$response = Json::decode($this->httpClient->getResult());
				if (!empty($response['error']) && $response['error']['code'] === 401)
				{
					$this->handleUnauthorize($this->connection);
					$result->addError(new Error($response['error']['message'], $response['error']['code']));
				}
				else
				{
					$result->addError(new Error('error of updating event', $this->httpClient->getStatus()));
				}
			}
		}
		catch (ArgumentException $e)
		{
			AddMessage2Log($e->getMessage(), 'calendar', 2, true);
			$result->addError(new Error('failed to update an event in google'));
		}
		catch (ObjectException $e)
		{
			AddMessage2Log($e->getMessage(), 'calendar', 2, true);
			$result->addError(new Error('failed to convert event'));
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @throws LoaderException
	 */
	public function delete(Core\Event\Event $event, EventContext $context): Result
	{
		$result = new Result();

		try
		{
			// TODO: Remake it: move this logic to parent::request().
			// Or, better, in separate class.
			$this->httpClient->query(
				HttpClient::HTTP_DELETE,
				$this->prepareUpdateUrl($context),
				$this->encode((new EventConverter($event))->convertForDelete())
			);

			if ($this->isRequestDeleteSuccess())
			{
				if ($response = $this->httpClient->getResult())
				{
					$requestResult = Json::decode($response);
					$result->setData($requestResult);
				}
			}
			else
			{
				$response = Json::decode($this->httpClient->getResult());
				if (!empty($response['error']) && $response['error']['code'] === 401)
				{
					$this->handleUnauthorize($this->connection);
					$result->addError(new Error($response['error']['message'], $response['error']['code']));
				}
				else
				{
					$result->addError(new Error('error of deleting event'));
				}
			}

		}
		catch (ArgumentException $e)
		{
			AddMessage2Log($e->getMessage(), 'calendar', 2, true);
			$result->addError(new Error('failed to delete an event in google'));
		}
		catch (ObjectException $e)
		{
			AddMessage2Log($e->getMessage(), 'calendar', 2, true);
			$result->addError(new Error('failed to convert event'));
		}

		return $result;
	}

	/**
	 * @throws LoaderException
	 * @throws ObjectException
	 * @throws Core\Base\BaseException
	 */
	public function createInstance(Core\Event\Event $event, EventContext $context): Result
	{
		$result = new Result();

		$instanceContext = $this->prepareContextForInstance($event, $context);
		if ($instanceContext === null)
		{
			AddMessage2Log('failed to create instance. id='. $event->getId(), 'calendar', 2, true);
			return $result->addError(new Error('failed to create an instance in google'));
		}

		try
		{
			// TODO: Remake it: move this logic to parent::request().
			// Or, better, in separate class.
			$this->httpClient->query(
				HttpClient::HTTP_PUT,
				$this->prepareUpdateUrl($instanceContext),
				$this->encode((new EventConverter($event, $instanceContext->getEventConnection()))->convertForUpdate())
			);

			if ($this->isRequestSuccess())
			{
				$requestResult = $this->prepareResult($this->httpClient->getResult(), $event);

				$result->setData($requestResult);
			}
			else
			{
				$response = Json::decode($this->httpClient->getResult());
				if (!empty($response['error']) && $response['error']['code'] === 401)
				{
					$this->handleUnauthorize($this->connection);
					$result->addError(new Error($response['error']['message'], $response['error']['code']));
				}
				else
				{
					$result->addError(new Error('error of creating instance'));
				}
			}
		}
		catch (ArgumentException $e)
		{
			AddMessage2Log($e->getMessage(), 'calendar', 2, true);
			$result->addError(new Error('failed to create an instance in google'));
		}

		return $result;
	}

	/**
	 * @throws Core\Base\BaseException
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

			$result = $this->update($event, $eventContext);
			if (!$result->isSuccess() && $result->getErrorCollection()->getErrorByCode(403))
			{
				// TODO: implement it
			}

			return $result;
		}

		return (new Result())->addError(new Error('Not found link for instance'));
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws Core\Base\BaseException
	 * @throws LoaderException
	 * @throws ObjectException
	 */
	public function deleteInstance(Event $event, EventContext $context): Result
	{
		$result = new Result();
		$excludeDate = $context->sync['excludeDate'] ?? null;
		$originalDate = $context->sync['originalDate'] ?? null;

		$instance = $this->getInstanceForDay($event, $excludeDate, $originalDate);
		$instanceContext = $this->prepareContextForInstance($instance, $context);

		if ($instanceContext === null)
		{
			AddMessage2Log('failed to create instance. id='. $event->getId(), 'calendar', 2, true);
			return $result->addError(new Error('failed to delete an instance in google'));
		}

		try
		{
			$this->httpClient->query(
				HttpClient::HTTP_PUT,
				$this->prepareUpdateUrl($instanceContext),
				$this->encode((new EventConverter($instance, $instanceContext->getEventConnection()))->convertForDeleteInstance())
			);

			if ($this->isRequestSuccess())
			{
				$requestResult = $this->prepareResult($this->httpClient->getResult(), $event);

				$result->setData($requestResult);
			}
			else
			{
				$response = Json::decode($this->httpClient->getResult());
				if (!empty($response['error']) && $response['error']['code'] === 401)
				{
					$this->handleUnauthorizeException();
				}

				$result->addError(new Error('error of delete instance'));
			}
		}

		catch (ArgumentException $e)
		{
			AddMessage2Log($e->getMessage(), 'calendar', 2, true);
			$result->addError(new Error('failed to delete an instance in google'));
		}

		return $result;
	}

	/**
	 * @param EventContext $context
	 * @return string
	 */
	public function prepareCreateUrl(EventContext $context): string
	{
		return Server::mapUri(
			$this->connection->getServer()->getFullPath() . self::CREATE_PATH,
			[
				'%CALENDAR_ID%' => Server::getEncodePath($context->getSectionConnection()->getVendorSectionId()),
			]
		);
	}

	/**
	 * @param EventContext $context
	 *
	 * @return string
	 */
	public function prepareUpdateUrl(EventContext $context): string
	{
		return Server::mapUri(
			$this->connection->getServer()->getFullPath() . self::EVENT_PATH,
			[
				'%CALENDAR_ID%' => Server::getEncodePath($context->getSectionConnection()->getVendorSectionId()),
				'%EVENT_ID%' => Server::getEncodePath($context->getEventConnection()->getVendorEventId()),
			]
		);
	}

	/**
	 * @param SyncEvent $recurrenceEvent
	 * @param SectionConnection $sectionConnection
	 * @param Context $context
	 *
	 * @return Result
	 * @throws Core\Base\BaseException
	 * @throws LoaderException
	 * @throws ObjectException
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
			$masterResult = $this->updateRecurrenceEntity($recurrenceEvent, $context);
		}
		else
		{
			$masterResult = $this->createRecurrenceEntity($recurrenceEvent, $sectionConnection, $context);
		}

		if (!$masterResult->isSuccess())
		{
			$result->addErrors($masterResult->getErrors());
			return $result;
		}

		if ($recurrenceEvent->getInstanceMap())
		{
			/** @var SyncEvent $instance */
			foreach ($recurrenceEvent->getInstanceMap()->getCollection() as $instance)
			{
				if ($instance->getEventConnection())
				{
					$instanceResult = $this->updateRecurrenceEntity($instance, $context, $recurrenceEvent->getEventConnection());
				}
				else
				{
					$instanceResult = $this->createRecurrenceEntity(
						$instance,
						$sectionConnection,
						$context,
						$recurrenceEvent->getEventConnection()
					);
				}

				if (!$result->isSuccess())
				{
					$result->addErrors($instanceResult->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param SyncEvent $recurrenceEvent
	 * @param SectionConnection $sectionConnection
	 * @param Context $context
	 *
	 * @return Result
	 * @throws Core\Base\BaseException
	 * @throws LoaderException
	 * @throws ObjectException
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
	 * @throws Core\Base\BaseException
	 * @throws LoaderException
	 * @throws ObjectException
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
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return EventContext|null
	 */
	public function prepareContextForInstance(Event $event, EventContext $context): ?EventContext
	{
		if (
			($context->getEventConnection() === null)
			|| ($context->getSectionConnection() === null)
		)
		{
			return null;
		}

		$masterVendorEventId = $context->getEventConnection()->getVendorEventId();

		if ($context->getEventConnection()->getEvent()->getVersion() > $event->getVersion())
		{
			$event->setVersion($context->getEventConnection()->getEvent()->getVersion());
		}

		return (new EventContext())
			->setSectionConnection($context->getSectionConnection())
			->setEventConnection(
				(new EventConnection())
					->setVendorEventId(
						$this->getInstanceId($masterVendorEventId, $event)
					)
					->setRecurrenceId($masterVendorEventId)
			);
	}

	/**
	 * @param string $masterId Vendor ID of master event
	 * @param Event $event exception event
	 *
	 * @return string
	 */
	private function getInstanceId(string $masterId, Event $event): string
	{
		$base = $masterId . '_';
		if ($event->isFullDayEvent())
		{
			return $base . $event->getOriginalDateFrom()->setTimezone(Util::prepareTimezone())->format('Ymd');
		}
		else
		{
			return $base . $event->getOriginalDateFrom()->setTimezone(Util::prepareTimezone())->format('Ymd\THis\Z');
		}
	}

	/**
	 * @param Event $event
	 * @param Core\Base\Date $excludeDate
	 *
	 * @return Event
	 */
	public function getInstanceForDay(
		Event $event,
		Core\Base\Date $excludeDate,
		?Core\Base\Date $originalDate = null
	): Event
	{
		$instanceEvent = clone $event;

		$instanceEvent->getStart()->getDate()->setDate(
			$excludeDate->getYear(),
			$excludeDate->getMonth(),
			$excludeDate->getDay()
		);
		$instanceEvent->getEnd()->getDate()->setDate(
			$excludeDate->getYear(),
			$excludeDate->getMonth(),
			$excludeDate->getDay()
		);
		$instanceEvent
			->setOriginalDateFrom($originalDate ?? $event->getStart())
			->setRecurringRule(null)
		;

		return $instanceEvent;
	}

	/**
	 * @param SyncEvent $syncEvent
	 * @param SectionConnection $sectionConnection
	 * @param Context $context
	 * @param EventConnection|null $masterLink
	 *
	 * @return Result
	 * @throws Core\Base\BaseException
	 * @throws LoaderException
	 * @throws ObjectException
	 */
	private function createRecurrenceEntity(
		SyncEvent $syncEvent,
		SectionConnection $sectionConnection,
		Context $context,
		EventConnection $masterLink = null
	): Result
	{
		$eventContext = new EventContext();
		$eventContext->merge($context);

		$eventContext->setSectionConnection($sectionConnection);

		if ($masterLink)
		{
			$eventContext->setEventConnection($masterLink);
			$result = $this->createInstance($syncEvent->getEvent(), $eventContext);
		}
		else
		{
			$result = $this->create($syncEvent->getEvent(), $eventContext);
		}

		if ($result->isSuccess())
		{
			if (!$syncEvent->getEvent()->isDeleted())
			{
				$link = (new EventConnection())
					->setEvent($syncEvent->getEvent())
					->setConnection($sectionConnection->getConnection())
					->setVersion($syncEvent->getEvent()->getVersion())
					->setVendorEventId($result->getData()['event']['id'])
					->setEntityTag($result->getData()['event']['etag'])
				;
				$syncEvent->setEventConnection($link);
			}
			else
			{
				$syncEvent->setAction(Dictionary::SYNC_EVENT_ACTION['delete']);
			}
		}

		return $result;
	}

	/**
	 * @param SyncEvent $syncEvent
	 * @param Context $context
	 * @param EventConnection|null $masterLink
	 *
	 * @return Result
	 * @throws Core\Base\BaseException
	 * @throws LoaderException
	 */
	private function updateRecurrenceEntity(
		SyncEvent $syncEvent,
		Context $context,
		EventConnection $masterLink = null
	): Result
	{
		$eventContext = new EventContext();
		$eventContext->merge($context);
		if ($masterLink)
		{
			$eventContext->setEventConnection($masterLink);
			$result = $this->updateInstance($syncEvent->getEvent(), $eventContext);
		}
		else
		{
			$eventContext->setEventConnection($syncEvent->getEventConnection());
			$result = $this->update($syncEvent->getEvent(), $eventContext);
		}
		if ($result->isSuccess())
		{
			$syncEvent->getEventConnection()->setEntityTag($result->getData()['event']['etag']);
		}

		return $result;
	}

	/**
	 * @throws ArgumentException
	 */
	private function encode(array $event)
	{
		return Json::encode($event, JSON_UNESCAPED_SLASHES);
	}

	/**
	 * @param string $result
	 * @param Event $event
	 *
	 * @return array
	 *
	 * @throws ArgumentException
	 */
	private function prepareResult(string $result, Event $event): array
	{
		$externalEvent = Json::decode($result);

		// $eventConnection = (new Sync\Google\Builders\BuilderEventConnectionFromExternalEvent($externalEvent, $event, $this->connection))->build();
		// $syncEvent =
		// 	(new Sync\Entities\SyncEvent())
		// 		->setEvent($event)
		// 		->setEventConnection($eventConnection)
		// ;

		return ['event' => [
			'id' => $externalEvent['id'],
			'etag' => $externalEvent['etag'],
			'version' => $externalEvent['etag'],
			'recurrence' => $externalEvent['recurringEventId'] ?? null,
		]];
	}

	private function handleUnauthorizeException()
	{
		$this->handleUnauthorize($this->connection);
	}
}
