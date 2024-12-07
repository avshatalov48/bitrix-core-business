<?php

namespace Bitrix\Calendar\EventCategory\Event;

use Bitrix\Calendar\EventCategory\Enum\EventType;
use Bitrix\Calendar\Internals\EventManager\BaseEvent;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber;

final class AfterEventCategoryCreate extends BaseEvent
{
	public function __construct(
		private readonly int $eventCategoryId,
		private readonly ?int $userId = null
	)
	{
	}

	public static function getEventType(): string
	{
		return EventType::AFTER_EVENT_CATEGORY_CREATE;
	}

	protected function getEventParams(): array
	{
		return [
			'eventCategoryId' => $this->eventCategoryId,
			'userId' => $this->userId,
		];
	}

	protected function getSubscribers(): array
	{
		return [
			new EventSubscriber\EventCategory\CreateOpenCategoryAttendee(),
			new EventSubscriber\EventCategory\SendPullAfterCreate(),
		];
	}
}
