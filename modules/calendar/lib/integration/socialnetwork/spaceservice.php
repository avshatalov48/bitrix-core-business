<?php

namespace Bitrix\Calendar\Integration\SocialNetwork;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Service;

class SpaceService
{
	public const SUPPORTED_EVENTS = [
		'invite',
		'onAfterCalendarEventDelete',
		'onCalendarEventCommentAdd',
	];

	/**
	 * @throws LoaderException
	 */
	public static function isAvailable(): bool
	{
		if (
			!class_exists(\Bitrix\Socialnetwork\Space\Service::class)
			|| !Loader::includeModule('socialnetwork')
		)
		{
			return false;
		}

		return \Bitrix\Socialnetwork\Space\Service::isAvailable();
	}

	public function addEvent(string $type, array $data): void
	{
		if (!static::isAvailable())
		{
			return;
		}

		if (!in_array($type, self::SUPPORTED_EVENTS, true))
		{
			return;
		}

		// enrich payload
		$data['RECEPIENTS'] = $this->getRecipientIds($data);
		Service::addEvent($this->mapTaskToSpaceEvent($type), $data);
	}

	private function mapTaskToSpaceEvent(string $type): string
	{
		$map = [
			'invite' => EventDictionary::EVENT_SPACE_CALENDAR_INVITE,
			'onAfterCalendarEventDelete' => EventDictionary::EVENT_SPACE_CALENDAR_EVENT_DEL,
			'onCalendarEventCommentAdd' => EventDictionary::EVENT_SPACE_CALENDAR_EVENT_COMMENT_ADD,
		];

		return $map[$type] ?? EventDictionary::EVENT_SPACE_CALENDAR_COMMON;
	}

	private function getRecipientIds(array $data): array
	{
		if (isset($data['TO_USER_ID']))
		{
			return [$data['TO_USER_ID']];
		}

		if (is_array($data['ATTENDEE_LIST'] ?? null))
		{
			return $this->getRecipientFromAttendeeList($data['ATTENDEE_LIST']);
		}

		return [];
	}

	private function getRecipientFromAttendeeList(array $attendeeList): array
	{
		$recipients = [];

		foreach ($attendeeList as $attendee)
		{
			if (!empty($attendee['id']))
			{
				$recipients[] = (int)$attendee['id'];
			}
		}

		return $recipients;
	}
}
