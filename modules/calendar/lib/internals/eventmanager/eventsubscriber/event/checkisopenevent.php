<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event;

use Bitrix\Calendar\Event\Enum\EventType;
use Bitrix\Calendar\Event\Event\AfterCalendarEventCreated;
use Bitrix\Calendar\Event\Event\AfterCalendarEventDeleted;
use Bitrix\Calendar\Event\Event\AfterCalendarEventEdited;
use Bitrix\Calendar\Event\Event\AfterOpenEventCreated;
use Bitrix\Calendar\Event\Event\AfterOpenEventDeleted;
use Bitrix\Calendar\Event\Event\AfterOpenEventEdited;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * Technical event subscriber.
 * If calendar event IS open_event, then emit relevant event.
 */
final class CheckIsOpenEvent implements EventSubscriberInterface
{
	use EventSubscriberResponseTrait;
	use CalendarEventSubscriberTrait;

	public function __invoke(Event $event): EventResult
	{
		$calendarEvent = $this->getCalendarEvent($event);
		if (!$calendarEvent)
		{
			return $this->makeUndefinedResponse();
		}

		$eventId = $calendarEvent->getId();

		if ($calendarEvent->isOpenEvent())
		{
			switch ($event->getEventType())
			{
				case EventType::AFTER_CALENDAR_EVENT_CREATED:
					$command = $event->getParameter('command');
					(new AfterOpenEventCreated($eventId, $command))->emit();
					break;
				case EventType::AFTER_CALENDAR_EVENT_EDITED:
					$command = $event->getParameter('command');
					(new AfterOpenEventEdited($eventId, $command))->emit();
					break;
				case EventType::AFTER_CALENDAR_EVENT_DELETED:
					(new AfterOpenEventDeleted($eventId))->emit();
					break;
			}
		}

		return $this->makeSuccessResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterCalendarEventCreated::class,
			AfterCalendarEventEdited::class,
			AfterCalendarEventDeleted::class,
		];
	}
}
