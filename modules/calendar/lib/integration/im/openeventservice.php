<?php

namespace Bitrix\Calendar\Integration\Im;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\Internals\Exception\ImException;

final class OpenEventService extends AbstractImService
{
	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function sendCalendarEventMessage(Event $event, EventCategory $eventCategory): int
	{
		$attach = $this->createMessageAttach($event);

		$messageId = \CIMChat::AddMessage([
			'FROM_USER_ID' => $event->getCreator()->getId(),
			'TO_CHAT_ID' => $eventCategory->getChannelId(),
			'MESSAGE' => $this->composeMessageFromEvent($event),
			'PARAMS' => [
				'ATTACH' => $attach,
			],
			'SKIP_USER_CHECK' => 'Y',
		]);

		if ($messageId === false)
		{
			throw new ImException('Cannot create message');
		}

		return $messageId;
	}

	public function updateCalendarEventMessage(Event $event): bool
	{
		$eventOptions = $event->getEventOption();
		$threadId = $eventOptions->getThreadId();

		$isUpdated = \CIMMessenger::Update(
			id: $eventOptions->getThreadId(),
			text: $this->composeMessageFromEvent($event)
		);
		$isDeleted = \CIMMessageParam::DeleteAll($threadId);
		$isAttachSet = \CIMMessageParam::Set($threadId, [$this->createMessageAttach($event)]);

		return $isUpdated && $isDeleted && $isAttachSet;
	}

	private function composeMessageFromEvent(Event $event): string
	{
		return $event->getName();
	}

	private function createMessageAttach(Event $event): \CIMMessageParamAttach
	{
		return \Bitrix\Calendar\Ui\Preview\Event::getImAttach(['eventId' => $event->getId()]);
	}
}
