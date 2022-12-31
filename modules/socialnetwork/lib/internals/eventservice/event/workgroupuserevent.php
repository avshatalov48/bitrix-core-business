<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Event;

use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventCollection;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\Internals\EventService\Push\WorkgroupUserSender;
use Bitrix\Socialnetwork\Internals\EventService\WorkgroupUserService;

/**
 * Class Event
 *
 * @package Bitrix\Socialnetwork\Internals\EventService\Event\WorkgroupUserEvent
 */

class WorkgroupUserEvent extends \Bitrix\Socialnetwork\Internals\EventService\Event
{
	/**
	 * @param array $data
	 * @return array
	 */
	protected function prepareData(array $data = []): array
	{
		$this->collectOldData();

		$validFields = [
			'GROUP_ID',
			'USER_ID',
			'ROLE',
		];

		foreach ($data as $key => $row)
		{
			if (!in_array($key, $validFields, true))
			{
				unset($data[$key]);
			}
		}

		return $data;
	}

	public function process(): void
	{
		$this->collectNewData();
		$events = (EventCollection::getInstance())->list();

		$workgroupsPushList = [];

		$workgroupUserEventsList = [
			EventDictionary::EVENT_WORKGROUP_USER_ADD,
			EventDictionary::EVENT_WORKGROUP_USER_UPDATE,
			EventDictionary::EVENT_WORKGROUP_USER_DELETE,
		];

		$added = [];
		$changed = [];
		$deleted = [];

		foreach ($events as $event)
		{
			/* @var $event Event */
			$eventType = $event->getType();
			$relationKey = $event->getRelationKey();

			if (in_array($eventType, $workgroupUserEventsList, true))
			{
				if ($eventType === EventDictionary::EVENT_WORKGROUP_USER_ADD)
				{
					$added[] = $relationKey;
				}
				elseif ($eventType === EventDictionary::EVENT_WORKGROUP_USER_UPDATE)
				{
					$changed[] = $relationKey;
				}
				elseif ($eventType === EventDictionary::EVENT_WORKGROUP_USER_DELETE)
				{
					$deleted[] = $relationKey;
				}
			}
		}

		$added = array_diff($added, $deleted);
		$changed = array_diff($changed, $added, $deleted);
		$changed = $this->clearNotRealChanges($changed);

		$newFields = $this->getNewFields();

		foreach ($events as $event)
		{
			/* @var $event Event */
			$eventType = $event->getType();
			$groupId = $event->getGroupId();
			$userId = $event->getGroupId();
			$relationKey = $event->getRelationKey();

			$role = ($newFields[$relationKey] ?? null);
			if (
				!$role
				|| (
					$eventType === EventDictionary::EVENT_WORKGROUP_USER_ADD
					&& !in_array($relationKey, $added, true)
				)
				|| (
					$eventType === EventDictionary::EVENT_WORKGROUP_USER_UPDATE
					&& !in_array($relationKey, $changed, true)
				)
			)
			{
				continue;
			}

			$workgroupUserPushList[] = [
				'EVENT' => $eventType,
				'GROUP_ID' => $groupId,
				'USER_ID' => $userId,
				'ROLE' => $role,
			];
		}

		if (!empty($workgroupUserPushList))
		{
			$notVisibleGroupsUsers = $this->getNotVisibleGroupsUsers([
				$added,
				$changed,
				$deleted,
			]);

			(new WorkgroupUserSender())->send($workgroupsPushList, $notVisibleGroupsUsers);
		}
	}

	protected function collectOldData(): void
	{
		$relationKey = $this->getRelationKey();
		$oldFields = $this->getOldFields();

		if (
			$relationKey !== ''
			&& !isset($oldFields[$relationKey])
		)
		{
			$oldFields[$relationKey] = UserToGroupTable::getList([
				'filter' => [
					'USER_ID' => $this->getUserId(),
					'GROUP_ID' => $this->getGroupId(),
				],
				'select' => [
					'ROLE',
					'GROUP_VISIBLE' => 'GROUP.VISIBLE',
				],
			])->fetch();
		}
	}

	protected function collectNewData(): void
	{
		$events = (EventCollection::getInstance())->list();
		$newFields = $this->getNewFields();

		foreach ($events as $event)
		{
			$relationKey = $event->getRelationKey();

			if (
				$relationKey !== ''
				&& !isset($newFields[$relationKey])
			)
			{
				$newFields[$relationKey] = UserToGroupTable::getList([
					'filter' => [
						'USER_ID' => $event->getUserId(),
						'GROUP_ID' => $event->getGroupId(),
					],
					'select' => [
						'ROLE',
						'GROUP_VISIBLE' => 'GROUP.VISIBLE',
					],
				])->fetch();
			}
		}

		$this->setNewFields($newFields);
	}

	private function getNotVisibleGroupsUsers(array $relationKeyList): array
	{
		$userList = [];

		if (empty($groupIds = $this->getNotVisibleGroupIds($relationKeyList)))
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

	private function getNotVisibleGroupIds($relationKeyList): array
	{
		[ $added, $changed, $deleted ] = $relationKeyList;

		$oldFields = $this->getOldFields();
		$newFields = $this->getNewFields();

		$filter = static function($relationKey) use ($newFields) {
			return ($newFields[$relationKey]['GROUP_VISIBLE'] === 'N');
		};
		$changedFilter = static function($relationKey) use ($oldFields, $newFields) {
			return (
				$newFields[$relationKey]['GROUP_VISIBLE'] === 'N'
				&& $oldFields[$relationKey]['GROUP_VISIBLE'] === 'N'
			);
		};
		$deletedFilter = static function($relationKey) use ($oldFields) {
			return ($oldFields[$relationKey]['GROUP_VISIBLE'] === 'N');
		};

		$relationKeyList = array_merge(
			array_filter($added, $filter),
			array_filter($changed, $changedFilter),
			array_filter($deleted, $deletedFilter),
		);

		return array_map(static function($relationKey) {
			[ $groupId, ] = explode('_', $relationKey);
			return $groupId;
		}, $relationKeyList);
	}

	private function clearNotRealChanges(array $changed): array
	{
		$realChanges = [
			'ROLE',
		];

		foreach ($changed as $key => $relationKey)
		{
			$changes = $this->getChanges($relationKey);
			if (!array_intersect_key($changes, array_flip($realChanges)))
			{
				unset($changed[$key]);
			}
		}

		return $changed;
	}

	protected function setOldFields($oldFields): void
	{
		WorkgroupUserService::getInstance()->setOldFields($oldFields);
	}

	protected function getOldFields(): array
	{
		return WorkgroupUserService::getInstance()->getOldFields();
	}

	protected function setNewFields($newFields): void
	{
		WorkgroupUserService::getInstance()->setNewFields($newFields);
	}

	protected function getNewFields(): array
	{
		return WorkgroupUserService::getInstance()->getNewFields();
	}
}
