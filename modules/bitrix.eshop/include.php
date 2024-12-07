<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;

class CEShop
{
	public static function ShowPanel(): void
	{
		/** @var CMain $APPLICATION */
		global $APPLICATION;
		/** @var CUser $USER */
		global $USER;

		if ($USER->IsAdmin() && Option::get('main', 'wizard_solution', '', SITE_ID) === 'eshop')
		{
			$APPLICATION->SetAdditionalCSS('/bitrix/wizards/bitrix/eshop/css/panel.css');

			$arMenu = [
				[
					"ACTION" => "jsUtils.Redirect([], '".CUtil::JSEscape("/bitrix/admin/wizard_install.php?lang=".LANGUAGE_ID."&wizardSiteID=".SITE_ID."&wizardName=bitrix:eshop&".bitrix_sessid_get())."')",
					"ICON" => "bx-popup-item-wizard-icon",
					"TITLE" => Loc::getMessage("STOM_BUTTON_TITLE_W1"),
					"TEXT" => Loc::getMessage("STOM_BUTTON_NAME_W1"),
				],
			];

			$APPLICATION->AddPanelButton([
				"HREF" => "/bitrix/admin/wizard_install.php?lang=".LANGUAGE_ID."&wizardName=bitrix:eshop&wizardSiteID=".SITE_ID."&".bitrix_sessid_get(),
				"ID" => "eshop_wizard",
				"ICON" => "bx-panel-site-wizard-icon",
				"MAIN_SORT" => 2500,
				"TYPE" => "BIG",
				"SORT" => 10,
				"ALT" => Loc::getMessage("SCOM_BUTTON_DESCRIPTION"),
				"TEXT" => Loc::getMessage("SCOM_BUTTON_NAME"),
				"MENU" => $arMenu,
			]);


			$request = Application::getInstance()->getContext()->getRequest();
			$uriString = $request->getRequestUri();
			$uri = new Uri($uriString);

			$APPLICATION->AddPanelButton([
				"ICON" => "bx-panel-themes-icon",
				"ALT" => Loc::getMessage("ESHOP_BUTTON_THEME_TEXT"),
				"TEXT" => Loc::getMessage("ESHOP_BUTTON_THEME"),
				"MAIN_SORT" => 1700,
				"MENU" => [
					[
						"TEXT" => Loc::getMessage("ESHOP_BUTTON_THEME_GREEN"),
						"ACTION" => "jsUtils.Redirect([], '".$uri->addParams(["theme" => "green"])."')",
					],
					[
						"TEXT" => Loc::getMessage("ESHOP_BUTTON_THEME_BLUE"),
						"ACTION" => "jsUtils.Redirect([], '".$uri->addParams(["theme" => "blue"])."')",
					],
					[
						"TEXT" => Loc::getMessage("ESHOP_BUTTON_THEME_YELLOW"),
						"ACTION" => "jsUtils.Redirect([], '".$uri->addParams(["theme" => "yellow"])."')",
					],
					[
						"TEXT" => Loc::getMessage("ESHOP_BUTTON_THEME_RED"),
						"ACTION" => "jsUtils.Redirect([], '".$uri->addParams(["theme" => "red"])."')",
					],
				],
				"MODE" => "view",
				"HINT" => [
					"TITLE" => Loc::getMessage("ESHOP_BUTTON_THEME"),
					"TEXT" => Loc::getMessage("ESHOP_BUTTON_THEME_TEXT"),
				],
			]);
		}
	}
}
