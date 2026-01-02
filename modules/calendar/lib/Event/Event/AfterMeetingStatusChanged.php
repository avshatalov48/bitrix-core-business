<?php

namespace Bitrix\Calendar\Event\Event;

use Bitrix\Calendar\Event\Enum\EventType;
use Bitrix\Calendar\Internals\EventManager\BaseEvent;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event\SyncChatParticipationWithEvent;

final class AfterMeetingStatusChanged extends BaseEvent
{
	public function __construct(
		private readonly int $parentEventId,
		private readonly int $userId,
		private readonly string $newStatus,
	)
	{
	}

	public static function getEventType(): string
	{
		return EventType::AFTER_MEETING_STATUS_CHANGED;
	}

	protected function getEventParams(): array
	{
		return [
			'eventId' => $this->parentEventId,
			'userId' => $this->userId,
			'newStatus' => $this->newStatus,
		];
	}

	protected function getSubscribers(): array
	{
		return [
			new SyncChatParticipationWithEvent(),
		];
	}
}
