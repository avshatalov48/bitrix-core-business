<?php
namespace Bitrix\Main\Analytics;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class SiteSpeed
{
	public static function onBuildGlobalMenu(&$arGlobalMenu, &$arModuleMenu)
	{
		$siteSpeedItem = array(
			"text" => Loc::getMessage("MAIN_ANALYTICS_MENU_SITE_SPEED"),
			"url" => "site_speed.php?lang=".LANGUAGE_ID,
			"more_url" => array("site_speed.php"),
			"title" => Loc::getMessage("MAIN_ANALYTICS_MENU_SITE_SPEED_ALT"),
		);

		$found = false;
		foreach ($arModuleMenu as &$arMenuItem)
		{
			if (!isset($arMenuItem["items_id"]) || $arMenuItem["items_id"] !== "menu_perfmon")
			{
				continue;
			}

			if (isset($arMenuItem["items"]) && is_array($arMenuItem["items"]))
			{
				array_unshift($arMenuItem["items"], $siteSpeedItem);
			}
			else
			{
				$arMenuItem["items"] = array($siteSpeedItem);
			}

			$found = true;
			break;
		}

		if (!$found)
		{
			$arModuleMenu[] = array(
				"parent_menu" => "global_menu_settings",
				"section" => "perfmon",
				"sort" => 1850,
				"text" => Loc::getMessage("MAIN_ANALYTICS_MENU_PERFORMANCE"),
				"title" => Loc::getMessage("MAIN_ANALYTICS_MENU_PERFORMANCE"),
				"icon" => "perfmon_menu_icon",
				"page_icon" => "perfmon_page_icon",
				"items_id" => "menu_perfmon",
				"items" => array($siteSpeedItem),
			);
		}
	}

	/**
	 * @return bool
	 */
	public static function isRussianSiteManager()
	{
		return (
			Directory::isDirectoryExists(Application::getDocumentRoot()."/bitrix/modules/main/lang/ru")
			|| Directory::isDirectoryExists(Application::getDocumentRoot()."/bitrix/modules/main/lang/ua")
		);
	}

	/**
	 * @param $siteId
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function isIntranetSite($siteId)
	{
		if (defined("ADMIN_SECTION") && ADMIN_SECTION === true)
		{
			return false;
		}

		$portalSiteList = [];
		$siteList = \Bitrix\Main\SiteTable::getList([
			"select" => ["LID"],
			"cache" => ["ttl" => 86400],
		])->fetchAll();
		foreach ($siteList as $site)
		{
			if (Option::get("main", "wizard_firstportal_".$site["LID"], false, $site["LID"]) !== false)
			{
				$portalSiteList[] = $site["LID"];
			}
			else if (Option::get("main", "wizard_firstbitrix24_".$site["LID"], false, $site["LID"]) !== false)
			{
				$portalSiteList[] = $site["LID"];
			}
		}

		if ($extranetSiteId = Option::get("extranet", "extranet_site", false))
		{
			$portalSiteList[] = $extranetSiteId;
		}

		return in_array($siteId, $portalSiteList);
	}

	public static function canGatherStat()
	{
		$enabled = !Application::getInstance()->getLicense()->isDemoKey();
		if($enabled)
		{
			$settings = Configuration::getValue("analytics_counter");
			if(isset($settings["enabled"]) && $settings["enabled"] === false)
			{
				$enabled = false;
			}
		}
		return $enabled;
	}

	public static function isOn()
	{
		return self::isRussianSiteManager() && self::canGatherStat();
	}
}