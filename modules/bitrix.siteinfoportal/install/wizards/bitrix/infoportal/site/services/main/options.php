<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (WIZARD_INSTALL_DEMO_DATA)	
{
	$arMenuTypes = GetMenuTypes();
	$arMenuTypes["bottom1"] = GetMessage("MAIN_OPT_MENU_BOTTOM_R");
	$arMenuTypes["bottom2"] = GetMessage("MAIN_OPT_MENU_BOTTOM_I");
	SetMenuTypes($arMenuTypes, WIZARD_SITE_ID);
}
?>