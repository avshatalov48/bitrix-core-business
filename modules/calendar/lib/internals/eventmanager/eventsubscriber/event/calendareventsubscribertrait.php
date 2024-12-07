<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event;

use Bitrix\Calendar\Core\Event\Event as CalendarEvent;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Event;

trait CalendarEventSubscriberTrait
{
	private function getCalendarEvent(Event $event): ?CalendarEvent
	{
		$eventId = (int)$event->getParameter('eventId');

		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');

		return $mapperFactory->getEvent()->getById($eventId);
	}
}
