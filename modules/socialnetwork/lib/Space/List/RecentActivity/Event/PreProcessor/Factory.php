<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\PreProcessor;

use Bitrix\Socialnetwork\Integration\Calendar\RecentActivity\CalendarPreProcessor;
use Bitrix\Socialnetwork\Integration\Tasks\RecentActivity\TaskPreProcessor;
use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor\ProcessorInterface;

final class Factory
{
	private const CALENDAR_EVENTS = [
		EventDictionary::EVENT_SPACE_CALENDAR_EVENT_UPD,
	];

	private const TASK_EVENTS = [
		EventDictionary::EVENT_SPACE_TASK_UPDATE,
	];

	private const LIVEFEED_EVENTS = [
		EventDictionary::EVENT_SPACE_LIVEFEED_POST_UPD,
	];

	private static Factory|null $instance = null;

	public static function getInstance(): Factory
	{
		if (!self::$instance)
		{
			self::$instance = new Factory();
		}

		return self::$instance;
	}

	private function __construct()
	{}

	public function createProcessor(Event $event): ?ProcessorInterface
	{
		$eventType = $event->getType();

		return match (true) {
			in_array($eventType, self::CALENDAR_EVENTS) => new CalendarPreProcessor($event),
			in_array($eventType, self::TASK_EVENTS) => new TaskPreProcessor($event),
			in_array($eventType, self::LIVEFEED_EVENTS) => new LiveFeedPreProcessor($event),
			default => new NullPreProcessor($event),
		};
	}
}