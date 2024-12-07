<?php

namespace Bitrix\Calendar\EventCategory\Event;

use Bitrix\Calendar\EventCategory\Enum\EventType;
use Bitrix\Calendar\Internals\EventManager\BaseEvent;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber;

final class AfterEventCategoryAttendeesDelete extends BaseEvent
{
	public function __construct(
		private readonly int $eventCategoryId,
		private readonly array $userIds
	)
	{
	}

	public static function getEventType(): string
	{
		return EventType::AFTER_EVENT_CATEGORY_ATTENDEES_DELETE;
	}

	protected function getEventParams(): array
	{
		return [
			'eventCategoryId' => $this->eventCategoryId,
			'userIds' => $this->userIds,
		];
	}

	protected function getSubscribers(): array
	{
		return [
			new EventSubscriber\EventCategory\DeleteBanCategory(),
		];
	}
}
