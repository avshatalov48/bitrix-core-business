<?php

namespace Bitrix\Calendar\EventCategory\Event;

use Bitrix\Calendar\EventCategory\Enum\EventType;
use Bitrix\Calendar\Internals\EventManager\BaseEvent;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber;

final class AfterEventCategoryUpdate extends BaseEvent
{
	public function __construct(
		private readonly int $eventCategoryId,
		private readonly array $fields = [],
		private readonly ?int $userId = null
	)
	{
	}

	public static function getEventType(): string
	{
		return EventType::AFTER_EVENT_CATEGORY_UPDATE;
	}

	protected function getEventParams(): array
	{
		return [
			'eventCategoryId' => $this->eventCategoryId,
			'fields' => $this->fields,
			'userId' => $this->userId,
		];
	}

	protected function getSubscribers(): array
	{
		return [
			new EventSubscriber\EventCategory\UpdateChannel(),
			new EventSubscriber\EventCategory\SendPullAfterUpdate(),
		];
	}
}