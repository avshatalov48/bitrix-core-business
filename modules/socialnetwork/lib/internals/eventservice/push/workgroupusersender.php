<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Internals\EventService\Push;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\UserToGroupTable;

class WorkgroupUserSender
{

	public function __construct()
	{

	}

	/**
	 * @param array $pushList
	 */
	public function send(array $pushList, array $notVisibleGroupsUsers): void
	{
		if (
			!ModuleManager::isModuleInstalled('pull')
			|| !Loader::includeModule('pull')
		)
		{
			return;
		}

		$tagList = [];
		foreach ($pushList as $push)
		{
			$tagList[] = PullDictionary::PULL_WORKGROUP_USERS_TAG_PREFIX . '_' . (int)$push['GROUP_ID'];
		}

		$tagList = array_unique($tagList);

		$query = \Bitrix\Pull\Model\WatchTable::query();
		$query->addSelect('USER_ID');
		$query->addSelect('TAG');
		$query->whereIn('TAG', $tagList);
		$records = $query->fetchAll();

		$subscribedUsers = [];
		foreach ($records as $record)
		{
			if (!preg_match('/^'. PullDictionary::PULL_WORKGROUP_USERS_TAG_PREFIX . '(\d+)$/i', $record['TAG'], $matches))
			{
				continue;
			}

			$groupId = (int)$matches[1];

			if (!isset($subscribedUsers[$groupId]))
			{
				$subscribedUsers[$groupId] = [];
			}

			$subscribedUsers[$groupId][] = (int)$record['USER_ID'];
		}

		foreach ($pushList as $push)
		{
			$pushCommand = PushEventDictionary::getPushEventType($push['EVENT']);
			$groupId = (int)$push['GROUP_ID'];
			$userId = (int)$push['USER_ID'];
			$role = (int)$push['ROLE'];

			$pushParams = [
				'module_id' => 'socialnetwork',
				'command' => $pushCommand,
				'params' => [ 'GROUP_ID' => $groupId ],
			];

			if ($role === UserToGroupTable::ROLE_REQUEST)
			{
				$recipients = [ $userId ];
			}
			elseif (!empty($subscribedUsers[$groupId]))
			{
				$recipients = (
					isset($notVisibleGroupsUsers[$groupId])
						? array_intersect($subscribedUsers[$groupId], $notVisibleGroupsUsers[$groupId])
						: $subscribedUsers[$groupId]
				);
			}

			PushSender::sendPersonalEvent($recipients, $pushCommand, $pushParams);
		}
	}
}
