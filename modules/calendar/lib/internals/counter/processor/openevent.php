<?php

namespace Bitrix\Calendar\Internals\Counter\Processor;

use Bitrix\Calendar\Core\Event\Event as CalendarEvent;
use Bitrix\Calendar\Internals\Counter\Event\Event;
use Bitrix\Calendar\Internals\Counter\Event\EventCollection;
use Bitrix\Calendar\Internals\Counter\Event\EventDictionary;

class OpenEvent implements Base
{
	private const SUPPORTED_EVENTS = [
		EventDictionary::OPEN_EVENT_CREATED,
		EventDictionary::OPEN_EVENT_DELETED,
		EventDictionary::OPEN_EVENT_SEEN,
	];

	public function process(): void
	{
		foreach (EventCollection::getInstance()->list() as $event)
		{
			/* @var $event Event */
			$eventType = $event->getType();
			if (!in_array($eventType, self::SUPPORTED_EVENTS, true))
			{
				continue;
			}

			switch ($eventType)
			{
				case EventDictionary::OPEN_EVENT_CREATED:
					$this->handleEventCreated($event);
					break;
				case EventDictionary::OPEN_EVENT_DELETED:
					$this->handleEventDeleted($event);
					break;
				case EventDictionary::OPEN_EVENT_SEEN:
					$this->handleEventSeen($event);
					break;
			}
		}
	}

	/**
	 * This method will be invoked by OpenEventAdded stepper
	 * it will add new counters for specified users
	 * it will emit a new event which will trigger a re-calculate process
	 * @param array $userIds
	 * @param CalendarEvent $event
	 * @return void
	 */
	public function upCounter(array $userIds, CalendarEvent $event): void
	{
		(new Handler\OpenEventUpCounter())($userIds, $event);
	}

	/**
	 * This method will be invoked by OpenEventDeleted stepper
	 * it will drop counters for specified users
	 * it will emit a new event which will trigger a re-calculate process
	 * @param array $userIds
	 * @param int $eventId
	 * @return void
	 */
	public function dropCounter(array $userIds, int $eventId, int $categoryId): void
	{
		(new Handler\OpenEventDropCounter())($userIds, $eventId, $categoryId);
	}

	private function handleEventCreated(Event $event): void
	{
		$eventId = (int)($event->getData()['event_id'] ?? null);

		(new Handler\OpenEventCreated())($eventId);
	}

	private function handleEventDeleted(Event $event): void
	{
		$eventId = (int)($event->getData()['event_id'] ?? null);
		$categoryId = (int)($event->getData()['category_id'] ?? null);

		(new Handler\OpenEventDeleted())($eventId, $categoryId);
	}

	private function handleEventSeen(Event $event): void
	{
		$categories = $event->getData()['categories'] ?? [];
		$userId = (int)($event->getData()['user_id'] ?? null);

		(new Handler\OpenEventSeen())($categories, $userId);
	}
}
