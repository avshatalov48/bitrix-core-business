<?php

namespace Bitrix\Calendar\Event\Event;

use Bitrix\Calendar\Application\Command\CreateEventCommand;
use Bitrix\Calendar\Event\Enum\EventType;
use Bitrix\Calendar\Internals\EventManager\BaseEvent;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber;

final class AfterCalendarEventCreated extends BaseEvent
{
	public function __construct(
		private readonly int $calendarEventId,
		private readonly CreateEventCommand $command
	)
	{
	}

	public static function getEventType(): string
	{
		return EventType::AFTER_CALENDAR_EVENT_CREATED;
	}

	protected function getEventParams(): array
	{
		return [
			'eventId' => $this->calendarEventId,
			'command' => $this->command,
		];
	}

	protected function getSubscribers(): array
	{
		return [
			new EventSubscriber\Event\CheckIsOpenEvent(),
			new EventSubscriber\Event\EventAnalytics(),
		];
	}
}
