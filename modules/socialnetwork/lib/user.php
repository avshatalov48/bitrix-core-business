<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class User
{
	static $moduleAdminListCache = array();

	public static function getModuleAdminList($siteIdList)
	{
		$cacheKey = serialize($siteIdList);
		if (!array_key_exists($cacheKey, self::$moduleAdminListCache))
		{
			$cache = new \CPHPCache;
			$cacheTime = 31536000;
			$cacheId = 'site'.($siteIdList ? '_'.implode('|', $siteIdList) : '').'new';
			$cachePath = "/sonet/user_admin/";

			$adminList = $moduleAdminList = array();

			if ($cache->initCache($cacheTime, $cacheId, $cachePath))
			{
				$cacheVars = $cache->getVars();
				$adminList = $cacheVars["ADMIN"];
				$moduleAdminList = $cacheVars["MODULE_ADMIN"];
			}
			else
			{
				$cache->startDataCache($cacheTime, $cacheId, $cachePath);

				$connection = Main\HttpApplication::getConnection();
				$helper = $connection->getSqlHelper();

				$sql = "SELECT 
					UG.USER_ID U_ID, 
					MAX(".\CDatabase::datetimeToTimestampFunction("UG.DATE_ACTIVE_FROM").") UG_DATE_FROM_TS, 
					MAX(".\CDatabase::datetimeToTimestampFunction("UG.DATE_ACTIVE_TO").") UG_DATE_TO_TS
					FROM 
						b_user_group UG 
					WHERE
						UG.GROUP_ID = 1
					GROUP BY 
						UG.USER_ID";

				$result = $connection->query($sql);

				while($ar = $result->fetch())
				{
					if(!array_key_exists($ar["U_ID"], $moduleAdminList))
					{
						$adminList[$ar["U_ID"]] = array(
							"USER_ID" => $ar["U_ID"],
							"DATE_FROM_TS" => $ar["UG_DATE_FROM_TS"],
							"DATE_TO_TS" => $ar["UG_DATE_TO_TS"]
						);
					}
				}

				if(!$siteIdList)
				{
					$sqlSite = "AND MG.SITE_ID IS NULL";
				}
				else
				{
					$sqlSite = " AND (";
					foreach($siteIdList as $i => $siteId)
					{
						if($i > 0)
						{
							$sqlSite .= " OR ";
						}

						$sqlSite .= "MG.SITE_ID " . ($siteId ? "= '" . $helper->forSQL($siteId) . "'" : "IS NULL");
					}
					$sqlSite .= ")";
				}

				$sql = "SELECT 
					UG.USER_ID U_ID, 
					G.ID G_ID, 
					MAX(".\CDatabase::datetimeToTimestampFunction("UG.DATE_ACTIVE_FROM").") UG_DATE_FROM_TS, 
					MAX(".\CDatabase::datetimeToTimestampFunction("UG.DATE_ACTIVE_TO").") UG_DATE_TO_TS, 
					MAX(MG.G_ACCESS) G_ACCESS 
					FROM 
						b_user_group UG, 
						b_module_group MG, 
						b_group G  
					WHERE
						G.ID = UG.GROUP_ID
						AND (
							G.ID = MG.GROUP_ID 
							AND MG.MODULE_ID = 'socialnetwork' 
							AND G.ACTIVE = 'Y' 
							AND G_ACCESS >= 'W' 
							AND (
								G.ANONYMOUS <> 'Y' 
								OR G.ANONYMOUS IS NULL
							) ".
							$sqlSite ."
						)
					GROUP BY 
						UG.USER_ID, G.ID";

				$result = $connection->query($sql);

				while($ar = $result->fetch())
				{
					if(!array_key_exists($ar["U_ID"], $moduleAdminList))
					{
						$moduleAdminList[$ar["U_ID"]] = array(
							"USER_ID" => $ar["U_ID"],
							"DATE_FROM_TS" => $ar["UG_DATE_FROM_TS"],
							"DATE_TO_TS" => $ar["UG_DATE_TO_TS"]
						);
					}
				}
			}

			$cacheData = Array(
				"ADMIN" => $adminList,
				"MODULE_ADMIN" => $moduleAdminList
			);

			$cache->endDataCache($cacheData);

			foreach ($adminList as $key => $arUserData)
			{
				if (
					(
						!empty($arUserData["DATE_FROM_TS"])
						&& $arUserData["DATE_FROM_TS"] > time()
					)
					|| (
						!empty($arUserData["DATE_TO_TS"])
						&& $arUserData["DATE_TO_TS"] < time()
					)
				)
				{
					unset($adminList[$key]);
				}
			}

			foreach ($moduleAdminList as $key => $arUserData)
			{
				if (
					(
						!empty($arUserData["DATE_FROM_TS"])
						&& $arUserData["DATE_FROM_TS"] > time()
					)
					|| (
						!empty($arUserData["DATE_TO_TS"])
						&& $arUserData["DATE_TO_TS"] < time()
					)
					|| (
						isset($adminList[$key])
						&& (
							empty($adminList[$key]["DATE_TO_TS"])
							|| $adminList[$key]["DATE_TO_TS"] > $arUserData["DATE_FROM_TS"]
						)
					)
				)
				{
					unset($moduleAdminList[$key]);
				}
			}

			self::$moduleAdminListCache[$cacheKey] = $adminList + $moduleAdminList;
		}

		return self::$moduleAdminListCache[$cacheKey];
	}
}
