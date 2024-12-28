<?php

namespace Bitrix\Calendar\Internals\Counter\Processor;

use Bitrix\Calendar\Internals\Counter\CounterDictionary;
use Bitrix\Calendar\Internals\Counter\Event\Event;
use Bitrix\Calendar\Internals\Counter\Event\EventCollection;
use Bitrix\Calendar\Internals\Counter\Event\EventDictionary;

class Invite implements Base
{
	private const SUPPORTED_EVENTS = [
		EventDictionary::EVENT_ATTENDEES_UPDATED,
		EventDictionary::COUNTERS_UPDATE,
	];

	public function process(): void
	{
		$events = (EventCollection::getInstance())->list();
		foreach ($events as $event)
		{
			/* @var $event Event */
			$eventType = $event->getType();

			if (in_array($eventType, self::SUPPORTED_EVENTS, true))
			{
				$affectedUsers = $event->getData()['user_ids'] ?? [];
				$this->recountInvites($affectedUsers);
			}
		}
	}

	private function recountInvites(array $users): void
	{
		if (empty($users))
		{
			return;
		}

		$counters = [];
		$events = $this->getEvents($users);

		foreach($events as $event)
		{
			$counters[$event['OWNER_ID']] = isset($counters[$event['OWNER_ID']])
				? $counters[$event['OWNER_ID']] + 1
				: 1
			;
		}

		foreach($users as $userId)
		{
			if($userId > 0)
			{
				$value = (isset($counters[$userId]) && $counters[$userId] > 0)
					? (int)$counters[$userId]
					: 0
				;

				\CUserCounter::Set($userId, CounterDictionary::COUNTER_INVITES, $value, '**', '', false);
			}
		}
	}

	private function getEvents(array $userIds): array
	{
		$events = \CCalendarEvent::GetList([
			'arFilter' => [
				'CAL_TYPE' => 'user',
				'OWNER_ID' => $userIds,
				'FROM_LIMIT' => \CCalendar::Date(time(), false),
				'TO_LIMIT' => \CCalendar::Date(time() + \CCalendar::DAY_LENGTH * 90, false),
				'IS_MEETING' => 1,
				'MEETING_STATUS' => 'Q',
				'DELETED' => 'N',
			],
			'parseRecursion' => false,
			'checkPermissions' => false
		]);

		return $events ?? [];
	}
}
