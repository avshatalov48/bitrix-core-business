<?php

namespace Bitrix\Calendar\Watcher\Membership\Handler;

use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Main\Loader;

class SocNetGroup extends Handler
{
	/**
	 * @param int $id
	 * @param array $arFields
	 * @return void
	 */
	public static function onSocNetUserToGroupAdd(int $id, array $arFields): void
	{
		if (
			empty($arFields['ROLE'])
			|| empty($arFields['GROUP_ID'])
			|| !Loader::includeModule("socialnetwork")
			|| self::isInvitee($arFields['ROLE'])
		)
		{
			return;
		}

		self::sendMessageToQueue(self::WORK_GROUP_TYPE, $arFields['GROUP_ID']);
	}

	/**
	 * @param int $id
	 * @param array $arFields
	 * @param array $oldUser2GroupArFields
	 * @return void
	 */
	public static function onSocNetUserToGroupUpdate(int $id, array $arFields, array $oldUser2GroupArFields): void
	{
		if (
			empty($oldUser2GroupArFields['GROUP_ID'])
			|| empty($oldUser2GroupArFields['ROLE'])
			||!Loader::includeModule("socialnetwork")
		)
		{
			return;
		}
		$groupId = $oldUser2GroupArFields['GROUP_ID'];
		$group = WorkgroupTable::getById($groupId)->fetch();

		if (empty($group['ID']) || !isset($group['PROJECT']))
		{
			return;
		}

		if(!self::isInvitee($oldUser2GroupArFields['ROLE']) && !self::isScrum($group['PROJECT']))
		{
			return;
		}

		self::sendMessageToQueue(self::WORK_GROUP_TYPE, $group['ID']);
	}

	/**
	 * @param int $id
	 * @param array $arFields
	 * @return void
	 */
	public static function onSocNetUserToGroupDelete(int $id, array $arFields): void
	{
		if (
			empty($arFields['ROLE'])
			|| empty($arFields['GROUP_ID'])
			||!Loader::includeModule("socialnetwork")
			|| self::isInvitee($arFields['ROLE'])
		)
		{
			return;
		}

		self::sendMessageToQueue(self::WORK_GROUP_TYPE, $arFields['GROUP_ID']);
	}

	/**
	 * @param int $id
	 * @param array $arFields
	 * @return void
	 */
	public static function onSocNetGroupUpdate(int $id, array $arFields): void
	{
		if (!isset($arFields['SCRUM_MASTER_ID']) || !Loader::includeModule("socialnetwork"))
		{
			return;
		}

		self::sendMessageToQueue(self::WORK_GROUP_TYPE, $id);
	}

	/**
	 * @param string $role
	 * @return bool
	 */
	private static function isInvitee(string $role): bool
	{
		return $role === \Bitrix\Socialnetwork\UserToGroupTable::ROLE_REQUEST;
	}

	/**
	 * @param string $projectField
	 * @return bool
	 */
	private static function isScrum(string $projectField): bool
	{
		return $projectField === 'Y';
	}
}