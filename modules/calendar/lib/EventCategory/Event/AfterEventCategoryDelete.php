<?php

namespace Bitrix\Calendar\EventCategory\Event;

use Bitrix\Calendar\EventCategory\Enum\EventType;
use Bitrix\Calendar\Internals\EventManager\BaseEvent;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber;

final class AfterEventCategoryDelete extends BaseEvent
{
	public function __construct(
		private readonly int $eventCategoryId,
	)
	{
	}

	public static function getEventType(): string
	{
		return EventType::AFTER_EVENT_CATEGORY_DELETE;
	}

	protected function getEventParams(): array
	{
		return [
			'eventCategoryId' => $this->eventCategoryId,
		];
	}

	protected function getSubscribers(): array
	{
		return [
			new EventSubscriber\EventCategory\SendPullAfterDelete(),
		];
	}
}