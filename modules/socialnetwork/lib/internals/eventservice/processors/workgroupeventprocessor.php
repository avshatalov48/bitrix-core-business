<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Processors;

use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventCollection;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Push\WorkgroupSender;
use Bitrix\Socialnetwork\UserToGroupTable;

class WorkGroupEventProcessor
{
	private array $groupOldFields = [];
	private array $groupNewFields = [];

	public function process(): void
	{
		$workgroupsPushList = [];

		$workgroupEventsList = [
			EventDictionary::EVENT_WORKGROUP_ADD,
			EventDictionary::EVENT_WORKGROUP_BEFORE_UPDATE,
			EventDictionary::EVENT_WORKGROUP_UPDATE,
			EventDictionary::EVENT_WORKGROUP_DELETE,
			EventDictionary::EVENT_WORKGROUP_USER_ADD,
			EventDictionary::EVENT_WORKGROUP_USER_UPDATE,
			EventDictionary::EVENT_WORKGROUP_USER_DELETE,
		];

		$addedEventTypes = [
			EventDictionary::EVENT_WORKGROUP_ADD,
		];
		$changedEventTypes = [
			EventDictionary::EVENT_WORKGROUP_BEFORE_UPDATE,
			EventDictionary::EVENT_WORKGROUP_UPDATE,
		];
		$deletedEventTypes = [
			EventDictionary::EVENT_WORKGROUP_DELETE,
		];
		$userChangedEventTypes = [
			EventDictionary::EVENT_WORKGROUP_USER_ADD,
			EventDictionary::EVENT_WORKGROUP_USER_UPDATE,
			EventDictionary::EVENT_WORKGROUP_USER_DELETE,
		];
		$userAddedAndDeletedEventTypes = [
			EventDictionary::EVENT_WORKGROUP_USER_ADD,
			EventDictionary::EVENT_WORKGROUP_USER_DELETE,
		];

		$added = [];
		$changed = [];
		$deleted = [];
		$userChanged = [];

		$events = (EventCollection::getInstance())->list();
		foreach ($events as $event)
		{
			/* @var Event $event */
			$event->collectNewData();
			$eventType = $event->getType();
			$groupId = $event->getGroupId();

			if (!in_array($eventType, $workgroupEventsList, true))
			{
				continue;
			}

			$this->groupOldFields[$groupId] = $event->getOldFields();
			$this->groupNewFields[$groupId] = $event->getNewFields();

			if (in_array($eventType, $addedEventTypes, true))
			{
				$added[] = $groupId;
			}
			elseif (in_array($eventType, $changedEventTypes, true))
			{
				$changed[] = $groupId;
			}
			elseif (in_array($eventType, $deletedEventTypes, true))
			{
				$deleted[] = $groupId;
			}
			elseif (in_array($eventType, $userChangedEventTypes, true))
			{
				$userChanged[] = $groupId;
			}
		}

		$added = array_diff($added, $deleted);
		$changed = array_diff($changed, $added, $deleted);
		$changed = $this->clearNotRealChanges($changed);
		$userChanged = array_diff($userChanged, $added, $changed, $deleted);

		$notVisibleGroupsUsers = $this->getNotVisibleGroupsUsers([
			$added,
			$changed,
			$deleted,
			$userChanged
		]);

		foreach ($events as $event)
		{
			/* @var Event $event */
			$eventType = $event->getType();
			$groupId = $event->getGroupId();
			$userId = $event->getUserId();

			if (
				in_array($eventType, $userAddedAndDeletedEventTypes, true)
				&& in_array($groupId, $userChanged, true)
			)
			{
				(new WorkgroupSender())->sendForUserAddedAndRemoved($event, $notVisibleGroupsUsers);
				unset($userChanged[$groupId]);
			}

			if (
				(
					in_array($eventType, $addedEventTypes, true)
					&& !in_array($groupId, $added, true)
				)
				|| (
					in_array($eventType, $changedEventTypes, true)
					&& !in_array($groupId, $changed, true)
				)
				|| (
					in_array($eventType, $userChangedEventTypes, true)
					&& !in_array($groupId, $userChanged, true)
				)
			)
			{
				continue;
			}

			$workgroupsPushList[] = [
				'EVENT' => $eventType,
				'GROUP_ID' => $groupId,
				'USER_ID' => $userId,
			];
		}

		if (!empty($workgroupsPushList))
		{
			(new WorkgroupSender())->send($workgroupsPushList, $notVisibleGroupsUsers);
		}
	}

	private function clearNotRealChanges(array $changed): array
	{
		$realChanges = [
			'NAME',
			'PROJECT_DATE_START',
			'PROJECT_DATE_FINISH',
			'IMAGE_ID',
			'AVATAR_TYPE',
			'OPENED',
			'CLOSED',
			'VISIBLE',
			'PROJECT',
			'KEYWORDS',
		];

		foreach ($changed as $key => $groupId)
		{
			$changes = $this->getChanges($groupId);
			if (!array_intersect_key($changes, array_flip($realChanges)))
			{
				unset($changed[$key]);
			}
		}

		return $changed;
	}

	private function getNotVisibleGroupsUsers(array $groupIds): array
	{
		$userList = [];

		if (empty($groupIds = $this->getNotVisibleGroupIds($groupIds)))
		{
			return $userList;
		}

		$relations = UserToGroupTable::getList([
			'select' => [ 'GROUP_ID', 'USER_ID' ],
			'filter' => [
				'@GROUP_ID' => $groupIds,
				'@ROLE' => UserToGroupTable::getRolesMember(),
			],
		])->fetchCollection();

		foreach ($relations as $relation)
		{
			$groupId = $relation->getGroupId();
			$userId = $relation->getUserId();

			if (!isset($userList[$groupId]))
			{
				$userList[$groupId] = [];
			}

			$userList[$groupId][] = $userId;
		}

		return $userList;
	}

	private function getNotVisibleGroupIds($groups): array
	{
		[ $added, $changed, $deleted, $userChanged ] = $groups;

		$oldFields = $this->groupOldFields;
		$newFields = $this->groupNewFields;

		$filter = function($groupId) use ($newFields) {
			return (($newFields[$groupId]['VISIBLE'] ?? null) === 'N');
		};
		$changedFilter = function($groupId) use ($oldFields, $newFields) {
			return (
				$newFields[$groupId]['VISIBLE'] === 'N'
				&& $oldFields[$groupId]['VISIBLE'] === 'N'
			);
		};
		$deletedFilter = function($groupId) use ($oldFields) {
			return ($oldFields[$groupId]['VISIBLE'] === 'N');
		};

		return array_merge(
			array_filter($added, $filter),
			array_filter($changed, $changedFilter),
			array_filter($deleted, $deletedFilter),
			array_filter($userChanged, $filter)
		);
	}

	private function getChanges(int $groupId): array
	{
		$oldFields = $this->groupOldFields[$groupId] ?? [];
		$newFields = $this->groupNewFields[$groupId] ?? [];

		$changes = [];

		foreach ($newFields as $key => $value)
		{
			if (mb_strpos($key, '~') === 0)
			{
				continue;
			}

			if (isset($oldFields[$key]) && $oldFields[$key] !== $value)
			{
				$changes[$key] = $value;
			}
		}

		return $changes;
	}
}