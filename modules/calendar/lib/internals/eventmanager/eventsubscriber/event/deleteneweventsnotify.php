<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event;

use Bitrix\Calendar\Event\Event\AfterOpenEventDeleted;
use Bitrix\Calendar\Internals\Counter\CounterService;
use Bitrix\Calendar\Internals\Counter\Event\EventDictionary;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class DeleteNewEventsNotify implements EventSubscriberInterface
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

		CounterService::addEvent(EventDictionary::OPEN_EVENT_DELETED, [
			'event_id' => $calendarEvent->getId(),
			'category_id' => $calendarEvent->getEventOption()->getCategoryId(),
		]);

		return $this->makeSuccessResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterOpenEventDeleted::class,
		];
	}
}
