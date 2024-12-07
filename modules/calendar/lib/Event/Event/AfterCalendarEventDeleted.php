<?php

namespace Bitrix\Calendar\Event\Event;

use Bitrix\Calendar\Event\Enum\EventType;
use Bitrix\Calendar\Internals\EventManager\BaseEvent;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber;

final class AfterCalendarEventDeleted extends BaseEvent
{
	public function __construct(
		private readonly int $calendarEventId,
	)
	{
	}

	public static function getEventType(): string
	{
		return EventType::AFTER_CALENDAR_EVENT_DELETED;
	}

	protected function getEventParams(): array
	{
		return [
			'eventId' => $this->calendarEventId,
		];
	}

	protected function getSubscribers(): array
	{
		return [
			new EventSubscriber\Event\CheckIsOpenEvent(),
		];
	}
}