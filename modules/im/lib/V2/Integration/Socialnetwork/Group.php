<?php

namespace Bitrix\Im\V2\Integration\Socialnetwork;

use Bitrix\Disk\Driver;
use Bitrix\Im\Integration\Socialnetwork\Extranet;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;

class Group
{
	public static function getStorageId(int $groupId): ?int
	{
		if (!Loader::includeModule('disk'))
		{
			return null;
		}

		return Driver::getInstance()->getStorageByGroupId($groupId)?->getId();
	}


	public static function getExtranetAccessibleUsersQuery(int $userId): ?Query
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return null;
		}

		$extranetSiteId = Option::get('extranet', 'extranet_site');
		$extranetSiteId = ($extranetSiteId && ModuleManager::isModuleInstalled('extranet') ? $extranetSiteId : false);

		if (
			!$extranetSiteId
			|| \CSocNetUser::isCurrentUserModuleAdmin()
		)
		{
			return null;
		}

		/** @see \Bitrix\Socialnetwork\Integration\UI\EntitySelector\UserProvider::EXTRANET_ROLES */
		$extranetRoles = [
			\Bitrix\Socialnetwork\UserToGroupTable::ROLE_USER,
			\Bitrix\Socialnetwork\UserToGroupTable::ROLE_OWNER,
			\Bitrix\Socialnetwork\UserToGroupTable::ROLE_MODERATOR,
		];

		$query = \Bitrix\Socialnetwork\UserToGroupTable::query();
		$query->addSelect(new ExpressionField('DISTINCT_USER_ID', 'DISTINCT %s', 'USER.ID'));
		$query->whereIn('ROLE', $extranetRoles);
		$query->registerRuntimeField(
			new Reference(
				'GS',
				\Bitrix\Socialnetwork\WorkgroupSiteTable::class,
				Join::on('ref.GROUP_ID', 'this.GROUP_ID')->where('ref.SITE_ID', $extranetSiteId),
				['join_type' => 'INNER']
			)
		);

		$query->registerRuntimeField(
			new Reference(
				'UG_MY',
				\Bitrix\Socialnetwork\UserToGroupTable::class,
				Join::on('ref.GROUP_ID', 'this.GROUP_ID')
					->where('ref.USER_ID', $userId)
					->whereIn('ref.ROLE', $extranetRoles),
				['join_type' => 'INNER']
			)
		);

		return $query;
	}

	public static function getUsersInSameGroups(int $userId): array
	{
		$groups = Extranet::getGroup([], $userId);
		$users = [];

		foreach ($groups as $group)
		{
			foreach ($group['USERS'] as $user)
			{
				$user = (int)$user;
				$users[$user] = $user;
			}
		}

		return array_values($users);
	}

	public static function filterAddedUsersToChatBySonetRestriction(array $userIds, int $currentUserId): array
	{
		if (
			$currentUserId === 0
			|| Loader::includeModule('intranet')
			|| !Loader::includeModule('socialnetwork')
			|| \CSocNetUser::IsFriendsAllowed()
		)
		{
			return $userIds;
		}

		$arFriendUsers = Array();
		$dbFriends = \CSocNetUserRelations::GetList(
			[],
			["USER_ID" => $currentUserId, "RELATION" => SONET_RELATIONS_FRIEND],
			false,
			false,
			["ID", "FIRST_USER_ID", "SECOND_USER_ID", "DATE_CREATE", "DATE_UPDATE", "INITIATED_BY"]
		);

		while ($arFriends = $dbFriends->Fetch())
		{
			$friendId = $currentUserId == $arFriends["FIRST_USER_ID"] ? $arFriends["SECOND_USER_ID"] : $arFriends["FIRST_USER_ID"];
			$arFriendUsers[$friendId] = $friendId;
		}

		foreach ($userIds as $id => $uid)
		{
			if ($uid == $currentUserId)
			{
				continue;
			}

			if (
				!isset($arFriendUsers[$uid])
				&& \CIMSettings::GetPrivacy(\CIMSettings::PRIVACY_CHAT, $uid) == \CIMSettings::PRIVACY_RESULT_CONTACT
			)
			{
				unset($userIds[$id]);
			}
		}

		return $userIds;
	}
}