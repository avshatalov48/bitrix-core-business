<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage blog
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Blog\Item;

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Blog
{
	public static function getByUser(array $params)
	{
		$result = false;

		if (
			!isset($params["USER_ID"])
			|| intval($params["USER_ID"]) <= 0

		)
		{
			return $result;
		}

		$siteId = (!empty($params["SITE_ID"]) ? $params["SITE_ID"] : SITE_ID);
		$userId = intval($params["USER_ID"]);
		$groupId = (!empty($params["GROUP_ID"])  ? $params["GROUP_ID"] : false);

		$cacheIdKeysList = array(
			"ACTIVE" => "Y",
			"USE_SOCNET" => (isset($params["USE_SOCNET"]) && $params["USE_SOCNET"] == "Y" ? "Y" : false),
			"GROUP_ID" => $groupId,
			"GROUP_SITE_ID" => $siteId,
			"OWNER_ID" => $userId,
		);

		$cacheTtl = 3153600;
		$cacheId = 'blog_post_blog_'.md5(serialize($cacheIdKeysList));
		$cacheDir = '/blog/form/blog/';

		$cache = new \CPHPCache;
		if($cache->initCache($cacheTtl, $cacheId, $cacheDir))
		{
			$result = $cache->getVars();
		}
		else
		{
			$cache->startDataCache();

			if ($groupId)
			{
				$blogFilter = [
					"=ACTIVE" => "Y",
					"GROUP_ID" => $groupId,
					"GROUP_SITE_ID" => $siteId,
					"OWNER_ID" => $userId
				];

				if (
					isset($params["USE_SOCNET"])
					&& $params["USE_SOCNET"] == "Y"
				)
				{
					$blogFilter["USE_SOCNET"] = "Y";
				}

				$res = \CBlog::getList([], $blogFilter);
				$result = $res->fetch();
			}

			if (
				!$result
				&& ModuleManager::isModuleInstalled("intranet")
			)
			{
				$ideaBlogGroupIdList = array();
				if (ModuleManager::isModuleInstalled("idea"))
				{
					$res = \CSite::getList("sort", "desc", Array("ACTIVE" => "Y"));
					while ($site = $res->fetch())
					{
						$val = Config\Option::get("idea", "blog_group_id", false, $site["LID"]);
						if ($val)
						{
							$ideaBlogGroupIdList[] = $val;
						}
					}
				}

				if (empty($ideaBlogGroupIdList))
				{
					$result = \CBlog::getByOwnerID($userId);
				}
				else
				{
					$blogGroupIdList = array();
					$res = \CBlogGroup::getList(array(), array(), false, false, array("ID"));
					while($blogGroup = $res->fetch())
					{
						if (!in_array($blogGroup["ID"], $ideaBlogGroupIdList))
						{
							$blogGroupIdList[] = $blogGroup["ID"];
						}
					}

					$result = \CBlog::getByOwnerID($userId, $blogGroupIdList);
				}
			}

			$cache->endDataCache($result);
		}

		if (
			!$result
			&& $groupId
			&& isset($params["CREATE"])
			&& $params["CREATE"] == "Y"
		)
		{
			$result = \Bitrix\Socialnetwork\ComponentHelper::createUserBlog(array(
				"BLOG_GROUP_ID" => $groupId,
				"USER_ID" => $userId,
				"SITE_ID" => $siteId
			));
		}

		return $result;
	}
}
