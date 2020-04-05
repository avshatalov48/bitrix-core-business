<?
IncludeModuleLangFile(__FILE__);
use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;

class CEShop
{
	public static function ShowPanel()
	{
		if ($GLOBALS["USER"]->IsAdmin() && COption::GetOptionString("main", "wizard_solution", "", SITE_ID) == "eshop")
		{
			$GLOBALS["APPLICATION"]->SetAdditionalCSS("/bitrix/wizards/bitrix/eshop/css/panel.css"); 

			$arMenu = Array(
				Array(		
					"ACTION" => "jsUtils.Redirect([], '".CUtil::JSEscape("/bitrix/admin/wizard_install.php?lang=".LANGUAGE_ID."&wizardSiteID=".SITE_ID."&wizardName=bitrix:eshop&".bitrix_sessid_get())."')",
					"ICON" => "bx-popup-item-wizard-icon",
					"TITLE" => GetMessage("STOM_BUTTON_TITLE_W1"),
					"TEXT" => GetMessage("STOM_BUTTON_NAME_W1"),
				)
			);

			$GLOBALS["APPLICATION"]->AddPanelButton(array(
				"HREF" => "/bitrix/admin/wizard_install.php?lang=".LANGUAGE_ID."&wizardName=bitrix:eshop&wizardSiteID=".SITE_ID."&".bitrix_sessid_get(),
				"ID" => "eshop_wizard",
				"ICON" => "bx-panel-site-wizard-icon",
				"MAIN_SORT" => 2500,
				"TYPE" => "BIG",
				"SORT" => 10,	
				"ALT" => GetMessage("SCOM_BUTTON_DESCRIPTION"),
				"TEXT" => GetMessage("SCOM_BUTTON_NAME"),
				"MENU" => $arMenu,
			));


			$request = Application::getInstance()->getContext()->getRequest();
			$uriString = $request->getRequestUri();
			$uri = new Uri($uriString);

			$GLOBALS["APPLICATION"]->AddPanelButton(
				array(
					"ICON" => "bx-panel-themes-icon",
					"ALT" => GetMessage("ESHOP_BUTTON_THEME_TEXT"),
					"TEXT" => GetMessage("ESHOP_BUTTON_THEME"),
					"MAIN_SORT" => 1700,
					"MENU" => array(
						array(
							"TEXT" => GetMessage("ESHOP_BUTTON_THEME_GREEN"),
							"ACTION" => "jsUtils.Redirect([], '".$uri->addParams(array("theme" => "green"))."')",
						),
						array(
							"TEXT" => GetMessage("ESHOP_BUTTON_THEME_BLUE"),
							"ACTION" => "jsUtils.Redirect([], '".$uri->addParams(array("theme" => "blue"))."')",
						),
						array(
							"TEXT" => GetMessage("ESHOP_BUTTON_THEME_YELLOW"),
							"ACTION" => "jsUtils.Redirect([], '".$uri->addParams(array("theme" => "yellow"))."')",
						),
						array(
							"TEXT" => GetMessage("ESHOP_BUTTON_THEME_RED"),
							"ACTION" => "jsUtils.Redirect([], '".$uri->addParams(array("theme" => "red"))."')",
						),
					),
					"MODE" => "view",
					"HINT" => array(
						"TITLE" => GetMessage("ESHOP_BUTTON_THEME"),
						"TEXT" => GetMessage("ESHOP_BUTTON_THEME_TEXT"),
					)
				)
			);
		}
	}
}