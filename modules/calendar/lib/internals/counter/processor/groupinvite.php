<?php

namespace Bitrix\Calendar\Internals\Counter\Processor;

use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Integration\Pull\PushCommand;
use Bitrix\Calendar\Integration\Pull\PushService;
use Bitrix\Calendar\Integration\SocialNetwork\Collab\counter\CollabListener;
use Bitrix\Calendar\Integration\SocialNetwork\UserGroupService;
use Bitrix\Calendar\Internals\Counter;
use Bitrix\Calendar\Internals\Counter\CounterDictionary;
use Bitrix\Calendar\Internals\Counter\Event\Event;
use Bitrix\Calendar\Internals\Counter\Event\EventCollection;
use Bitrix\Calendar\Internals\Counter\Event\EventDictionary;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\SystemException;

final class GroupInvite implements Base
{
	private const SUPPORTED_EVENTS = [
		EventDictionary::EVENT_ATTENDEES_UPDATED,
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
				$eventData = $event->getData();
				$affectedUserIds = $eventData['user_ids'] ?? [];
				$affectedEventIds = $eventData['event_ids'] ?? [];
				$affectedGroupIds = $eventData['group_ids'] ?? [];
				if (!$affectedUserIds || (!$affectedEventIds && !$affectedGroupIds))
				{
					continue;
				}

				$this->recountGroupEventsInvites($affectedUserIds, $affectedEventIds, $affectedGroupIds);
			}
		}
	}

	/**
	 * @param int[] $userIds
	 * @param int[] $eventIds
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function recountGroupEventsInvites(array $userIds, array $eventIds, array $groupIds): void
	{
		$groupsToRecount = [];

		if (!empty($eventIds))
		{
			$parentIdsOfAffectedEvents = $this->getParentIdsOfAffectedEvents($eventIds);
			$groupsToRecount = $this->getAffectedGroupIds($parentIdsOfAffectedEvents);
		}

		if (!empty($groupIds))
		{
			$groupsToRecount = array_unique([...$groupsToRecount, ...$groupIds]);
		}

		if (empty($groupsToRecount))
		{
			return;
		}

		// filter affected groups, keep those to which affected users belong
		$userAffectedGroups = [];
		foreach ($userIds as $userId)
		{
			if ($userId <= 0)
			{
				continue;
			}

			// get all user groups and check if user belongs any affected group
			$userGroups = array_keys(UserGroupService::getInstance()->getUserGroups($userId));
			$affectedGroupsForUser = array_intersect($userGroups, $groupsToRecount);
			if (!$userGroups || !$affectedGroupsForUser)
			{
				continue;
			}

			$userAffectedGroups[$userId] = $affectedGroupsForUser;
		}

		// get unique group ids which needs to recount for some of affected users
		$groupsToRecountForAffectedUsers = array_unique(array_values(array_merge(...$userAffectedGroups)));
		if (!$groupsToRecountForAffectedUsers)
		{
			return;
		}

		$groupsToNotify = [];

		// preload all actual events from affected groups and sort it by groupId
		$eventsByGroup = $this->prepareGroupEvents($groupsToRecountForAffectedUsers);

		foreach ($userIds as $userId)
		{
			if ($userId <= 0 || !($userAffectedGroups[$userId] ?? []))
			{
				continue;
			}

			foreach ($userAffectedGroups[$userId] as $groupId)
			{
				$parentGroupEvents = $eventsByGroup[$groupId] ?? [];
				if (!$parentGroupEvents)
				{
					$this->cleanGroupCounter($userId, $groupId);

					continue;
				}

				$groupsToNotify[$groupId] ??= [];
				$groupsToNotify[$groupId][] = $userId;

				$this->updateGroupCounter($userId, $groupId, $parentGroupEvents);
			}
		}

		if (!empty($groupsToNotify))
		{
			(new CollabListener())->notify($groupsToNotify);
		}
	}

	/**
	 * @param int $userId
	 * @param int $groupId
	 *
	 * @return void
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 */
	private function cleanGroupCounter(int $userId, int $groupId): void
	{
		\CUserCounter::Set(
			user_id: $userId,
			code: $this->getGroupCounterCode($groupId),
			value: 0,
			site_id: '**',
			sendPull: false,
		);

		$this->sendGroupCountersPush($userId, $groupId);
	}

	/**
	 * @param int $userId
	 * @param int $groupId
	 * @param int[] $parentIds
	 *
	 * @return void
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function updateGroupCounter(int $userId, int $groupId, array $parentIds): void
	{
		$actualValue = $this->getActualUserInvitesCount($userId, $parentIds);

		$counterCode = $this->getGroupCounterCode($groupId);
		$storedValue = \CUserCounter::GetValue(
			user_id: $userId,
			code: $counterCode,
		);

		if ((!$actualValue && !$storedValue) || $actualValue === $storedValue)
		{
			return;
		}

		\CUserCounter::Set(
			user_id: $userId,
			code: $counterCode,
			value: $actualValue,
			site_id: '**',
			sendPull: false,
		);

		$this->sendGroupCountersPush($userId, $groupId);
	}

	/**
	 * @param int[] $eventIds
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getParentIdsOfAffectedEvents(array $eventIds): array
	{
		return array_unique(
			EventTable::query()
				->whereIn('ID', $eventIds)
				->where('IS_MEETING', 1)
				->setSelect(['PARENT_ID'])
				->fetchCollection()
				->getParentIdList()
		);
	}

	/**
	 * @param int[] $eventIds
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getAffectedGroupIds(array $eventIds): array
	{
		return array_unique(
			EventTable::query()
				->whereIn('ID', $eventIds)
				->where('CAL_TYPE', Dictionary::CALENDAR_TYPE['group'])
				->where('IS_MEETING', 1)
				->setSelect(['OWNER_ID'])
				->fetchCollection()
				->getOwnerIdList()
		);
	}

	/**
	 * @param array $allGroupIds
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function prepareGroupEvents(array $allGroupIds): array
	{
		$groupEvents = $this->getGroupsEvents($allGroupIds);
		$eventsByGroup = [];

		foreach ($groupEvents as $eventId => $groupId)
		{
			$eventsByGroup[(int)$groupId][] = $eventId;
		}

		return $eventsByGroup;
	}

	/**
	 * @param array $groupIds
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getGroupsEvents(array $groupIds): array
	{
		[$fromTimestamp, $toTimestamp] = $this->getPeriod();

		$result = EventTable::query()
			->whereIn('OWNER_ID', $groupIds)
			->where('CAL_TYPE', Dictionary::CALENDAR_TYPE['group'])
			->where('DATE_FROM_TS_UTC', '>=', $fromTimestamp)
			->where('DATE_TO_TS_UTC', '<=', $toTimestamp)
			->where('IS_MEETING', 1)
			->where('MEETING_STATUS', 'H')
			->setSelect(['ID', 'OWNER_ID'])
			->fetchCollection();

		return array_combine($result->getIdList(), $result->getOwnerIdList());
	}

	/**
	 * @param int $userId
	 * @param int[] $parentIds
	 *
	 * @return int
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getActualUserInvitesCount(int $userId, array $parentIds): int
	{
		[$fromTimestamp, $toTimestamp] = $this->getPeriod();

		return (int)EventTable::query()
			->where('OWNER_ID', $userId)
			->whereIn('PARENT_ID', $parentIds)
			->where('CAL_TYPE', Dictionary::CALENDAR_TYPE['user'])
			->where('DATE_FROM_TS_UTC', '>=', $fromTimestamp)
			->where('DATE_TO_TS_UTC', '<=', $toTimestamp)
			->where('IS_MEETING', 1)
			->where('MEETING_STATUS', 'Q')
			->where('DELETED', 'N')
			->registerRuntimeField('COUNT', new ExpressionField('COUNT', 'COUNT(*)'))
			->setSelect(['COUNT'])
			->fetch()['COUNT']
		;
	}

	private function getPeriod(): array
	{
		$fromTimestamp = (int)\CCalendar::Timestamp(\CCalendar::Date(time(), false), false);
		$toTimestamp = (int)\CCalendar::Timestamp(\CCalendar::Date(time() + \CCalendar::DAY_LENGTH * 90, false), false)
			+ \CCalendar::GetDayLen()
			- 1;

		return [$fromTimestamp, $toTimestamp];
	}

	/**
	 * @param int $userId
	 * @param int $groupId
	 *
	 * @return void
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 */
	private function sendGroupCountersPush(int $userId, int $groupId): void
	{
		PushService::addEvent($userId, [
			'module_id' => PushService::MODULE_ID,
			'command' => PushCommand::UpdateGroupCounters->value,
			'params' => [
				'groupId' => $groupId,
				'counters' => [
					CounterDictionary::COUNTER_GROUP_INVITES => Counter::getInstance($userId)->get(CounterDictionary::COUNTER_GROUP_INVITES, $groupId),
				],
			],
		]);
	}

	/**
	 * @param int $groupId
	 *
	 * @return string
	 */
	private function getGroupCounterCode(int $groupId): string
	{
		return sprintf(CounterDictionary::COUNTER_GROUP_INVITES_TPL, $groupId);
	}
}
