<?php

namespace Bitrix\Calendar\Event\Helper;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Event\EventRepository;
use Bitrix\Main\DI\ServiceLocator;

final class EventHelper
{
	/**
	 * Tech helper method to update broken event attendees counters.
	 */
	public static function calcAndUpdateEventAttendeesCount(array $eventIds): void
	{
		/** @var Factory $mapper */
		$mapper = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$eventMapper = $mapper->getEvent();
		$eventOptionsMapper = $mapper->getEventOption();
		foreach ($eventIds as $eventId)
		{
			/** @var Event $evt */
			$evt = $eventMapper->getById($eventId);
			$count = EventRepository::getEventAttendeesCount($eventId);
			$eventOptions = $evt->getEventOption();
			$eventOptions->setAttendeesCount($count);
			$eventOptionsMapper->update($eventOptions, ['updateAttendeesCounter' => true]);
		}
	}

	public static function getViewUrl(Event $event): string
	{
		return \CCalendar::getEntryUrl(
			$event->getCalendarType(),
			$event->getOwner()?->getId() ?? 0,
			$event->getId(),
			$event->getStart()->format('d.m.Y H:i:s'),
		);
	}
}
