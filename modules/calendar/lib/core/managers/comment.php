<?php

namespace Bitrix\Calendar\Core\Managers;

use Bitrix\Calendar\Integration\SocialNetwork\SpaceService;

final class Comment
{
	private SpaceService $spaceService;

	public function __construct()
	{
		$this->spaceService = new SpaceService();
	}

	public function onEventCommentAdd(array $data): void
	{
		$this->spaceService->addEvent(
			'onCalendarEventCommentAdd',
			$data,
		);
	}

	public static function onCommentDeleteHandler($event): void
	{
		if ($event instanceof \Bitrix\Main\Event)
		{
			(new self())->onCommentDelete($event);
		}
	}

	public function onCommentDelete(\Bitrix\Main\Event $calendarEvent): void
	{
		// TODO do not use before event getting rework (perms check)
		return;
		[$type, $eventId, $messageData] = $calendarEvent->getParameters();

		if ($type !== 'EV' || !is_numeric($eventId) || empty($messageData['MESSAGE_ID']))
		{
			return;
		}

		$calendarEvent = \CCalendarEvent::GetById($eventId);
		if (is_array($calendarEvent))
		{
			$this->spaceService->addEvent(
				'onCalendarEventCommentDelete',
				[
					'ID' => (int)$eventId,
					'COMMENT_ID' => (int)$messageData['MESSAGE_ID'],
					'ATTENDEE_LIST' => $calendarEvent['ATTENDEE_LIST'] ?? null,
					'ATTENDEES_CODES' => $calendarEvent['ATTENDEES_CODES'] ?? null,
				],
			);
		}
	}
}