<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event;

use Bitrix\Calendar\Event\Event\AfterOpenEventCreated;
use Bitrix\Calendar\Event\Service\OpenEventPullService;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class SendPullAfterCreate implements EventSubscriberInterface
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

		OpenEventPullService::getInstance()->createCalendarEvent($calendarEvent);

		return $this->makeSuccessResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterOpenEventCreated::class,
		];
	}
}
