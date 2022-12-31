<?php

namespace Bitrix\Socialnetwork\Component\WorkgroupList;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Component\WorkgroupList;
use Bitrix\Tasks\Internals\Counter\CounterDictionary;
use Bitrix\Tasks\UI\ScopeDictionary;

class TasksCounter
{
	public static function getAccessToTasksCounters(array $params = []): bool
	{
		$mode = ($params['mode'] ?? '');
		$contextUserId = (int)($params['contextUserId'] ?? 0);
		$currentUserId = (int)($params['currentUserId'] ?? \Bitrix\Socialnetwork\Helper\User::getCurrentUserId());

		if (
			!Loader::includeModule('tasks')
			|| !in_array($mode, WorkgroupList::getTasksModeList(), true)
		)
		{
			return false;
		}

		return (
			$currentUserId === $contextUserId
			|| \Bitrix\Tasks\Util\User::isSuper($currentUserId)
			|| \CTasks::IsSubordinate($contextUserId, $currentUserId)
		);
	}

	public static function getTasksCounters(array $params = []): array
	{
		$mode = ($params['mode'] ?? '');

		$result = [];

		if (
			!Loader::includeModule('tasks')
			|| !in_array($mode, WorkgroupList::getTasksModeList(), true)
		)
		{
			return $result;
		}

		switch ($mode)
		{
			case WorkgroupList::MODE_TASKS_PROJECT:
				$result = [
					CounterDictionary::COUNTER_SONET_TOTAL_EXPIRED,
					CounterDictionary::COUNTER_SONET_TOTAL_COMMENTS,
					CounterDictionary::COUNTER_SONET_FOREIGN_EXPIRED,
					CounterDictionary::COUNTER_SONET_FOREIGN_COMMENTS,
				];
				break;
			case WorkgroupList::MODE_TASKS_SCRUM:
				$result = [
					CounterDictionary::COUNTER_SCRUM_TOTAL_COMMENTS,
					CounterDictionary::COUNTER_SCRUM_FOREIGN_COMMENTS,
				];
				break;
			default:
		}

		return $result;
	}

	public static function getTasksCountersScope(array $params = []): string
	{
		$mode = ($params['mode'] ?? '');

		$result = '';

		if (
			!Loader::includeModule('tasks')
			|| !in_array($mode, WorkgroupList::getTasksModeList(), true)
		)
		{
			return $result;
		}

		switch ($mode)
		{
			case WorkgroupList::MODE_TASKS_PROJECT:
				$result = ScopeDictionary::SCOPE_PROJECTS_GRID;
				break;
			case WorkgroupList::MODE_TASKS_SCRUM:
				$result = ScopeDictionary::SCOPE_SCRUM_PROJECTS_GRID;
				break;
			default:
		}

		return $result;
	}
}
