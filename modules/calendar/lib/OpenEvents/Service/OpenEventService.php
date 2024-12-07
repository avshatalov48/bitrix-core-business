<?php

namespace Bitrix\Calendar\OpenEvents\Service;

use Bitrix\Calendar\Event\Service\OpenEventPullService;
use Bitrix\Calendar\Internals\Counter\CounterService;
use Bitrix\Calendar\Internals\Counter\Event\EventDictionary;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Integration\Im\OpenEventService as ImIntegration;
use Bitrix\Main\DI\ServiceLocator;

final class OpenEventService
{
	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function onOpenEventEdited(int $eventId): void
	{
		$event = $this->getEventById($eventId);

		ImIntegration::getInstance()->updateCalendarEventMessage($event);
		OpenEventPullService::getInstance()->updateCalendarEvent($event);
	}

	/**
	 * @param int[] $eventIds
	 */
	public function setEventsWatched(int $userId, array $eventIds): void
	{
		$eventsByCategory = [];

		foreach ($eventIds as $eventId)
		{
			$categoryId = $this->getEventById($eventId)->getEventOption()->getCategoryId();
			$eventsByCategory[$categoryId][] = $eventId;
		}

		CounterService::addEvent(EventDictionary::OPEN_EVENT_SEEN, [
			'categories' => $eventsByCategory,
			'user_id' => $userId,
		]);
	}

	private function getEventById(int $eventId): Event
	{
		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');

		return $mapperFactory->getEvent()->getById($eventId);
	}

	private function __construct()
	{
	}
}
