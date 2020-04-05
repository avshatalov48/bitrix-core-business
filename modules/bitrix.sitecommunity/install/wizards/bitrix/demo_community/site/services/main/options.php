<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (WIZARD_INSTALL_DEMO_DATA)	
{      
	/*$arMenuTypes = GetMenuTypes();
	if (!array_key_exists("bottom", $arMenuTypes))
		$arMenuTypes["bottom"] = GetMessage("MAIN_OPT_MENU_BOTTOM");

	SetMenuTypes($arMenuTypes, WIZARD_SITE_ID);*/            
	
	COption::SetOptionString("socialnetwork", "allow_calendar_user", "N", false, WIZARD_SITE_ID);
	COption::SetOptionString("socialnetwork", "allow_calendar_group", "N", false, WIZARD_SITE_ID);
}
?>