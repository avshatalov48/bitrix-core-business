<?php
namespace Bitrix\Im\Integration\Intranet;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Department
{
	const CACHE_TOKEN_TTL = 2592000; // 1 month

	public static function checkModules()
	{
		return
			Loader::includeModule('intranet')
			&& Loader::includeModule("socialnetwork")
		;
	}

	public static function getList(?array $ids = null)
	{
		if (!self::checkModules())
		{
			return false;
		}

		if (\Bitrix\Im\User::getInstance()->isExtranet())
		{
			return [];
		}

		if (!empty($ids))
		{
			$departments = \Bitrix\Im\V2\Integration\HumanResources\Department\Department::getInstance()->getListByIds($ids);
		}
		else
		{
			$departments = \Bitrix\Im\V2\Integration\HumanResources\Department\Department::getInstance()->getList();
		}

		$result = [];
		foreach ($departments as $department)
		{
			$result[$department->id] = [
				'ID' => $department->id,
				'NAME' => $department->name,
				'FULL_NAME' => $department->name,
				'MANAGER_USER_ID' => $department->headUserID,
			];

			if (
				$department->depthLevel > 0
				&& isset($result[$department->parent]['FULL_NAME'])
			)
			{
				$result[$department->id]['FULL_NAME'] = $department->name . ' / ' . $result[$department->parent]['FULL_NAME'];
			}
		}

		return $result;
	}

	public static function getGroup($params)
	{
		if (!self::checkModules())
			return false;

		$params = is_array($params)? $params: Array();

		if (!isset($params['CURRENT_USER']) && is_object($GLOBALS['USER']))
		{
			$params['CURRENT_USER'] = $GLOBALS['USER']->GetID();
		}

		$userId = intval($params['CURRENT_USER']);
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

		$groups = Array();
		$groupIds = Array();
		while ($row = $db->GetNext(true, false))
		{
			$groupIds[] = $row["GROUP_ID"];
			$groups['SG'.$row['GROUP_ID']] = array(
				'ID' => 'SG'.$row['GROUP_ID'],
				'NAME' => Loc::getMessage('IM_INT_SN_GROUP_EXTRANET', Array('#GROUP_NAME#' => $row['GROUP_NAME'])),
				'USERS' => Array()
			);

			$taggedCache->registerTag('sonet_group_'.$row['GROUP_ID']);
			$taggedCache->registerTag('sonet_user2group_G'.$row['GROUP_ID']);
		}

		if (count($groups) <= 0)
		{
			return false;
		}

		$taggedCache->endTagCache();

		$db = \CSocNetUserToGroup::GetList(
			array(),
			array(
				"GROUP_ID" => $groupIds,
				"<=ROLE" => SONET_ROLES_USER,
				"USER_ACTIVE" => "Y",
				"USER_CONFIRM_CODE" => false
			),
			false,
			false,
			array("ID", "USER_ID", "GROUP_ID")
		);
		while ($ar = $db->GetNext(true, false))
		{
			if($ar["USER_ID"] == $userId)
				continue;

			$groups['SG'.$row['GROUP_ID']]['USERS'][] = $ar["USER_ID"];
		}

		$cache->startDataCache();
		$cache->endDataCache($groups);

		return $groups;
	}
}
