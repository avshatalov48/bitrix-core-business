<?php

namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Connection\Server;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Managers\EventManagerInterface;
use Bitrix\Calendar\Sync\Util\EventContext;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Main\Error;
use Generator;

class EventManager extends AbstractManager implements EventManagerInterface
{
	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \CDavArgumentNullException
	 */
	public function create(Event $event, EventContext $context): Result
	{
		$result = new Result();

		$data = $this->getApiService()->createEvent($context->getSectionConnection()->getVendorSectionId(), $event);

		if ($this->getApiService()->getError())
		{
			$this->processConnectionError($this->connection, $this->getApiService()->getError());
		}

		if ($data && is_array($data))
		{
			$result->setData([
				'event' => [
					'id' => $data['XML_ID'],
					'version' => $data['MODIFICATION_LABEL'],
					'etag' => $data['MODIFICATION_LABEL'],
				],
			]);
		}
		else
		{
			$result->addError(new Error('Error while trying to save event'));
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \CDavArgumentNullException
	 */
	public function update(Event $event, EventContext $context): Result
	{
		$result = new Result();
		$event->setUid($context->getEventConnection()->getVendorEventId());

		if ($event->getRecurringRule())
		{
			return $this->saveInstance($event, $context);
		}

		$data = $this->getApiService()->updateEvent(
			$context->getSectionConnection()->getVendorSectionId(),
			$event,
			$context->getEventConnection()->getData()
		);

		if ($this->getApiService()->getError())
		{
			$this->processConnectionError($this->connection, $this->getApiService()->getError());
		}

		if ($data && is_array($data))
		{
			$result->setData([
				'event' => [
					'id' => $data['XML_ID'],
					'version' => $data['MODIFICATION_LABEL'],
					'etag' => $data['MODIFICATION_LABEL'],
				],
			]);
		}
		else
		{
			$result->addError(new Error('Error while trying to update event'));
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \CDavArgumentNullException
	 */
	public function delete(Event $event, EventContext $context): Result
	{
		$result = new Result();
		$sectionId = $context->getSectionConnection()->getVendorSectionId();
		$event->setUid($context->getEventConnection()->getVendorEventId());

		$data = $this->getApiService()->deleteEvent($sectionId, $event);

		if ($this->getApiService()->getError())
		{
			$this->processConnectionError($this->connection, $this->getApiService()->getError());
		}
		
		if (!$data)
		{
			$result->addError(new Error('Error while trying delete event'));
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \CDavArgumentNullException
	 */
	public function createInstance(Event $event, EventContext $context): Result
	{
		return $this->saveInstance($event, $context);
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \CDavArgumentNullException
	 */
	public function updateInstance(Event $event, EventContext $context): Result
	{
		return $this->saveInstance($event, $context);
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \CDavArgumentNullException
	 */
	private function saveInstance(Event $event, EventContext $context): Result
	{
		$result = new Result();
		$masterLink = $context->getEventConnection();

		if (!$masterLink)
		{
			$result->addError(new Error('Master link not found'));

			return $result;
		}

		$sectionId = $context->getSectionConnection()->getVendorSectionId();
		$masterEvent = $masterLink->getEvent();
		$masterEvent->setUid($masterLink->getVendorEventId());

		$data = $this->getApiService()->saveInstance(
			$sectionId,
			$masterEvent,
			$masterLink->getData()
		);

		if ($this->getApiService()->getError())
		{
			$this->processConnectionError($this->connection, $this->getApiService()->getError());
		}

		if ($data && is_array($data))
		{
			$result->setData([
				'event' => [
					'id' => $data['XML_ID'],
					'version' => $data['MODIFICATION_LABEL'],
					'etag' => $data['MODIFICATION_LABEL'],
				],
			]);
		}
		else
		{
			$result->addError(new Error('Error while trying to save instance'));
		}

		return $result;
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \CDavArgumentNullException
	 */
	public function deleteInstance(Event $event, EventContext $context): Result
	{
		$result = new Result();
		$masterLink = $context->getEventConnection();

		if (!$masterLink)
		{
			$result->addError(new Error('Master link not found'));

			return $result;
		}

		$sectionId = $context->getSectionConnection()->getVendorSectionId();
		$excludeDate = $context->sync['excludeDate'];
		$masterEvent = $masterLink->getEvent();
		$masterEvent->setUid($masterLink->getVendorEventId());

		$data = $this->getApiService()->saveInstance(
			$sectionId,
			$masterEvent,
			$masterLink->getData(),
			$excludeDate
		);

		if ($this->getApiService()->getError())
		{
			$this->processConnectionError($this->connection, $this->getApiService()->getError());
		}

		if ($data && is_array($data))
		{
			$result->setData([
				'event' => [
					'id' => $data['XML_ID'],
					'version' => $data['MODIFICATION_LABEL'],
					'etag' => $data['MODIFICATION_LABEL'],
				],
			]);
		}
		else
		{
			$result->addError(new Error('Error while trying to delete instance'));
		}

		return $result;
	}

	/**
	 * @param SyncEvent $recurrenceEvent
	 * @param SectionConnection $sectionConnection
	 * @param Context $context
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \CDavArgumentNullException
	 */
	public function saveRecurrence(
		SyncEvent $recurrenceEvent,
		SectionConnection $sectionConnection,
		Context $context
	): Result
	{
		$result = new Result();
		$sectionId = $context->sync['vendorSectionId'];

		$data = $this->getApiService()->saveRecurrence($sectionId, $recurrenceEvent);

		if ($this->getApiService()->getError())
		{
			$this->processConnectionError($sectionConnection->getConnection(), $this->getApiService()->getError());
		}

		if ($data && is_array($data))
		{
			$masterEvent = $recurrenceEvent;

			$this->prepareLink($masterEvent, $sectionConnection, $data['XML_ID'], $data['MODIFICATION_LABEL']);

			/** @var SyncEvent $instance */
			foreach ($recurrenceEvent->getInstanceMap()->getCollection() as $instance)
			{
				$this->prepareLink($instance, $sectionConnection, $data['XML_ID']);
				$instance->getEventConnection()->setRecurrenceId($data['XML_ID']);
			}
		}
		else
		{
			$result->addError(new Error('Error while trying to save recurrence event'));
		}

		return $result;
	}

	/**
	 * @param SyncEvent $recurrenceEvent
	 * @param SectionConnection $sectionConnection
	 * @param Context $context
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \CDavArgumentNullException
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
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \CDavArgumentNullException
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
	 * @return ApiService
	 */
	private function getApiService(): ApiService
	{
		if (!$this->apiService)
		{
			$this->apiService = new ApiService();
		}

		return $this->apiService;
	}

	/**
	 * @param Calendar\Sync\Entities\SyncEvent $event
	 * @param SectionConnection $connection
	 * @param string $vendorId
	 * @param string|null $entityTag
	 *
	 * @return void
	 */
	private function prepareLink(
		SyncEvent $event,
		SectionConnection $connection,
		string $vendorId,
		?string $entityTag = null
	): void
	{
		if ($event->getEventConnection())
		{
			$event->getEventConnection()
				->setLastSyncStatus(Calendar\Sync\Dictionary::SYNC_STATUS['success'])
				->setEntityTag($entityTag);
		}
		else
		{
			$link = (new EventConnection())
				->setId(null)
				->setEvent($event->getEvent())
				->setVersion($event->getEvent()->getVersion())
				->setConnection($connection->getConnection())
				->setVendorEventId($vendorId)
				->setEntityTag($entityTag)
				->setLastSyncStatus(Calendar\Sync\Dictionary::SYNC_STATUS['success'])
			;
			$event->setEventConnection($link);
		}
	}

	/**
	 * @param Connection $connection
	 * @param array $error
	 * @return void
	 * @throws \CDavArgumentNullException
	 */
	private function processConnectionError(Connection $connection, array $error): void
	{
		$parsedError = '[' . $error[0] . '] ' . $error[1];
		\CDavConnection::SetLastResult($connection->getId(), $parsedError);
	}
}
