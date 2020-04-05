<?php
namespace Bitrix\Main\Analytics;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\ModuleManager;

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

	public static function isRussianSiteManager()
	{
		return
			!ModuleManager::isModuleInstalled("intranet") &&
			(
				Directory::isDirectoryExists(Application::getDocumentRoot()."/bitrix/modules/main/lang/ru") ||
				Directory::isDirectoryExists(Application::getDocumentRoot()."/bitrix/modules/main/lang/ua")
			)
		;
	}

	public static function canGatherStat()
	{
		$enabled = (defined("LICENSE_KEY") && LICENSE_KEY !== "DEMO");
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