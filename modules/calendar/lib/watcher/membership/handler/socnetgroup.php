<?php

namespace Bitrix\Calendar\Watcher\Membership\Handler;

use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Main\Loader;

class SocNetGroup extends Handler
{
	private static array $groups = [];

	/**
	 * @param int $id
	 * @param array $arFields
	 *
	 * @return void
	 * @throws \Bitrix\Main\LoaderException
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
	 *
	 * @return void
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
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
		$group = self::getGroup($groupId);

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
	 * @return array|false|mixed
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getGroup(int $groupId): mixed
	{
		if (isset(self::$groups[$groupId]))
		{
			return self::$groups[$groupId];
		}

		self::$groups[$groupId] = WorkgroupTable::query()
			->setSelect(['ID', 'PROJECT'])
			->where('ID', $groupId)
			->exec()->fetch()
		;

		return self::$groups[$groupId];
	}

	/**
	 * @param int $id
	 * @param array $arFields
	 *
	 * @return void
	 * @throws \Bitrix\Main\LoaderException
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
	 *
	 * @return void
	 * @throws \Bitrix\Main\LoaderException
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
