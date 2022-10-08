<?php

namespace Bitrix\Sale\BsmSiteMaster\Tools;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main,
	Bitrix\Main\Config\Configuration;

/**
 * Class SitePatcher
 * @package Bitrix\Sale\BsmSiteMaster\Tools
 */
class SitePatcher
{
	const HIDE_PANEL_FOR_USERS = "hide_panel_for_users";
	const ALL_USERS_ACCESS_CODE = "G2";
	const FORCE_ENABLE_SELF_HOSTED_COMPOSITE = "force_enable_self_hosted_composite";

	/**
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function unsetG2GroupFromHidePanel()
	{
		$hidePanelForUsers = Main\Config\Option::get("main", self::HIDE_PANEL_FOR_USERS);
		if (CheckSerializedData($hidePanelForUsers) && $hidePanelForUsers = unserialize($hidePanelForUsers, ['allowed_classes' => false]))
		{
			$hidePanelForUsers = array_filter($hidePanelForUsers, function($group) {
				return $group !== self::ALL_USERS_ACCESS_CODE;
			});

			Main\Config\Option::set("main", self::HIDE_PANEL_FOR_USERS, serialize($hidePanelForUsers));
		}
	}

	/**
	 * Enable composite using option in .settings.php
	 */
	public static function enableComposite()
	{
		if (self::isCanEnableComposite())
		{
			Configuration::setValue(self::FORCE_ENABLE_SELF_HOSTED_COMPOSITE, true);
		}
	}

	/**
	 * @return bool
	 */
	private static function isCanEnableComposite()
	{
		if (Configuration::getValue(self::FORCE_ENABLE_SELF_HOSTED_COMPOSITE) === false)
		{
			return false;
		}

		return true;
	}
}