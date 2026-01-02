<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event;

use Bitrix\Calendar\Event\Event\AfterMeetingStatusChanged;
use Bitrix\Calendar\Integration\Im\ChatService;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class SyncChatParticipationWithEvent implements EventSubscriberInterface
{
	use CalendarEventSubscriberTrait;
	use EventSubscriberResponseTrait;

	public function __invoke(Event $event): EventResult
	{
		$newStatus = $event->getParameter('newStatus');

		$calendarEvent = $this->getCalendarEvent($event);
		if (!$calendarEvent || $calendarEvent->getRecurrenceId())
		{
			// not found or recurring instance event
			return $this->makeUndefinedResponse();
		}

		$chatId = $calendarEvent->getMeetingDescription()?->getFields()['CHAT_ID'] ?? null;

		if (!$chatId)
		{
			// chat for event not created yet
			return $this->makeUndefinedResponse();
		}

		$result = match ($newStatus)
		{
			'N' => ChatService::getInstance()->deleteUserFromChat($event->getParameter('userId'), (int)$chatId),
			'Y' => ChatService::getInstance()->addUserToChat($event->getParameter('userId'), (int)$chatId),
			default => null,
		};

		return $result ? $this->makeSuccessResponse() : $this->makeErrorResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterMeetingStatusChanged::class,
		];
	}
}
