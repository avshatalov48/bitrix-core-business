<?php
namespace Bitrix\Im\Integration\Socialnetwork;

use Bitrix\Main\Localization\Loc;

class Extranet
{
	const CACHE_TOKEN_TTL = 2592000; // 1 month

	public static function checkModules()
	{
		return \Bitrix\Main\Loader::includeModule('extranet') && \Bitrix\Main\Loader::includeModule("socialnetwork");
	}

	public static function getGroup($params, $userId = null)
	{
		if (!self::checkModules())
			return false;

		$params = is_array($params)? $params: [];

		$userId = \Bitrix\Im\Common::getUserId($userId);
		if ($userId <= 0)
		{
			return false;
		}

		$cacheId = 'im_sonet_extranet_v3_'.$userId;
		$cachePath = '/bx/imc/sonet/extranet'.\Bitrix\Im\Common::getCacheUserPostfix($userId);

		$cache = \Bitrix\Main\Application::getInstance()->getCache();
		$taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();

		if($cache->initCache(self::CACHE_TOKEN_TTL, $cacheId, $cachePath))
		{
			return $cache->getVars();
		}

		$cache->startDataCache();

		$taggedCache->startTagCache($cachePath);

		$db = \CSocNetUserToGroup::GetList(
			array(),
			array(
				"USER_ID" => $userId,
				"<=ROLE" => SONET_ROLES_USER,
				"GROUP_SITE_ID" => \CExtranet::GetExtranetSiteID(),
				"GROUP_ACTIVE" => "Y",
				"GROUP_CLOSED" => "N"
			),
			false,
			false,
			array("ID", "GROUP_ID", "GROUP_NAME")
		);

		$groups = [];
		$groupIds = [];
		while ($row = $db->GetNext(true, false))
		{
			$groupIds[] = $row["GROUP_ID"];
			$groups['SG'.$row['GROUP_ID']] = array(
				'ID' => 'SG'.$row['GROUP_ID'],
				'NAME' => Loc::getMessage('IM_INT_SN_GROUP_EXTRANET', Array('#GROUP_NAME#' => $row['GROUP_NAME'])),
				'USERS' => []
			);

			$taggedCache->registerTag('sonet_group_'.$row['GROUP_ID']);
			$taggedCache->registerTag('sonet_user2group_G'.$row['GROUP_ID']);
		}

		if (count($groups) <= 0)
		{
			return false;
		}

		$taggedCache->registerTag('sonet_user2group');

		$taggedCache->endTagCache();

		$db = \CSocNetUserToGroup::GetList(
			array(),
			array(
				"@GROUP_ID" => $groupIds,
				"<=ROLE" => SONET_ROLES_USER,
				"USER_ACTIVE" => "Y",
				"USER_CONFIRM_CODE" => false
			),
			false,
			false,
			array("ID", "USER_ID", "GROUP_ID")
		);
		while ($row = $db->GetNext(true, false))
		{
			if($row["USER_ID"] == $userId || !isset($groups['SG'.$row['GROUP_ID']]))
				continue;

			$groups['SG'.$row['GROUP_ID']]['USERS'][] = $row["USER_ID"];
		}

		$cache->endDataCache($groups);

		return $groups;
	}

	public static function isUserInGroup($userId, $currentUserId = null)
	{
		$currentUserId = \Bitrix\Im\Common::getUserId($currentUserId);
		if ($currentUserId <= 0)
		{
			return false;
		}

		if ($userId == $currentUserId)
		{
			return true;
		}

		$extranetUsers = [];
		$groups = self::getGroup([], $currentUserId);
		if (is_array($groups))
		{
			foreach ($groups as $group)
			{
				foreach ($group['USERS'] as $uid)
				{
					$extranetUsers[$uid] = $uid;
				}
			}
		}

		return isset($extranetUsers[$userId]);
	}

	public static function filterUserList(array $userList, $currentUserId = null)
	{
		$currentUserId = \Bitrix\Im\Common::getUserId($currentUserId);
		if ($currentUserId <= 0)
		{
			return false;
		}

		if (empty($userList))
		{
			return [];
		}

		$extranetUsers = [];
		$groups = self::getGroup([], $currentUserId);
		if (is_array($groups))
		{
			foreach ($groups as $group)
			{
				foreach ($group['USERS'] as $uid)
				{
					$extranetUsers[$uid] = $uid;
				}
			}
		}

		return array_filter($userList, function($userId) use ($extranetUsers) {
			return isset($extranetUsers[$userId]);
		});
	}
}



