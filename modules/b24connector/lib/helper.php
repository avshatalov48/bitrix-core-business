<?php
namespace Bitrix\B24Connector;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Helper.
 * Different useful staff.
 * @package Bitrix\B24Connector
 */
class Helper
{
	/**
	 * Builds admin menu
	 * @param array $aGlobalMenu
	 * @param array $aModuleMenu
	 * @return array|bool Global menu item.
	 */
	public static function onBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
	{
		global $APPLICATION;

		$moduleAccess = $APPLICATION->GetGroupRight('b24connector');

		if($moduleAccess < "R")
			return false;

		$menu = array(
			"parent_menu" => "global_menu_b24connector",
			"section" => "b24connector",
			"sort" => 100,
			"text" => Loc::getMessage("B24C_HLP_INTEGRATION"),
			"icon" => "b24connector_menu_icon",
			"page_icon" => "b24connector_page_icon",
			"items_id" => "menu_b24connector",
			"url" => "b24connector_b24connector.php?lang=".LANGUAGE_ID,
			"more_url" => array("b24connector_chat.php", "sale_crm.php"),
			"items" => array(),
		);

		$menu["items"][] = array(
			"text" => Loc::getMessage("B24C_HLP_BUTTONS"),
			"url" => "/bitrix/admin/b24connector_buttons.php?lang=".LANGUAGE_ID,
			"icon" => "b24connector_menu_icon_butt",
			"more_url" => array(
				"b24connector_buttons.php"
			)
		);

		$menu["items"][] = array(
			"text" => Loc::getMessage("B24C_HLP_CHAT"),
			"url" => "/bitrix/admin/b24connector_chat.php?lang=".LANGUAGE_ID,
			"icon" => "b24connector_menu_icon_chat",
			"more_url" => array(
				"b24connector_chat.php"
			)
		);

		$menu["items"][] = array(
			"text" => Loc::getMessage('B24C_HLP_RECALL'),
			"url" => "/bitrix/admin/b24connector_recall.php?lang=".LANGUAGE_ID,
			"icon" => "b24connector_menu_icon_recall",
			"more_url" => array(
				"b24connector_recall.php"
			)
		);

		$menu["items"][] = array(
			"text" => Loc::getMessage('B24C_HLP_CRM_FORM'),
			"url" => "/bitrix/admin/b24connector_crm_forms.php?lang=".LANGUAGE_ID,
			"icon" => "b24connector_menu_icon_cform",
			"more_url" => array(
				"b24connector_crm_forms.php"
			)
		);

		$menu["items"][] = array(
			"text" => Loc::getMessage('B24C_HLP_OL'),
			"url" => "/bitrix/admin/b24connector_open_lines.php?lang=".LANGUAGE_ID,
			"icon" => "b24connector_menu_icon_ol",
			"more_url" => array(
				"b24connector_open_lines.php"
			)
		);

		$menu["items"][] = array(
			"text" => Loc::getMessage('B24C_HLP_TELEPHONY'),
			"url" => "/bitrix/admin/b24connector_telefonia.php?lang=".LANGUAGE_ID,
			"icon" => "b24connector_menu_icon_telephony",
			"more_url" => array(
				"b24connector_telefonia.php"
			)
		);

		return array(
			"global_menu_b24connector" => array(
				"menu_id" => "b24connector",
				"text" => Loc::getMessage("B24C_HLP_GM_TEXT"),
				"title" => Loc::getMessage("B24C_HLP_GM_TITLE"),
				"sort" => 210,
				"items_id" => "global_menu_b24connector",
				"help_section" => "b24connector",
				"items" => array($menu)
			));
	}

	/**
	 * Insert JS on public pages
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function onBeforeProlog()
	{
		global $APPLICATION;

		if(defined("ADMIN_SECTION") && ADMIN_SECTION === true)
			return;

		if (defined('B24CONNECTOR_SKIP') && B24CONNECTOR_SKIP === true)
		{
			return;
		}

		if($connection = Connection::getFields())
		{
			$result = '';

			$dbRes = ButtonTable::getList(array(
				'filter' => array(
					'=APP_ID' => $connection['ID']
				)
			));

			while($row = $dbRes->fetch())
			{
				if(strlen($row['SCRIPT']) > 0)
					$result .= $row['SCRIPT']."\n";
			}

			if(strlen($result) > 0)
			{
				\Bitrix\Main\Page\Asset::getInstance()->addString($result, false, \Bitrix\Main\Page\AssetLocation::BODY_END);

				ob_start();
				$APPLICATION->IncludeComponent("bitrix:b24connector.openline.info", "", Array("COMPOSITE_FRAME_TYPE" => "STATIC"));
				$saoRes = ob_get_contents();
				ob_end_clean();

				\Bitrix\Main\Page\Asset::getInstance()->addString($saoRes, false, \Bitrix\Main\Page\AssetLocation::BODY_END);
			}
		}
	}
}