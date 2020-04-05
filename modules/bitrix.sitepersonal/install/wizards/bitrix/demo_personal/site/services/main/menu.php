<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

	CModule::IncludeModule('fileman');
	$arMenuTypes = GetMenuTypes(WIZARD_SITE_ID);
	if($arMenuTypes['left'] && $arMenuTypes['left'] == GetMessage("WIZ_MENU_LEFT_DEFAULT"))
		$arMenuTypes['left'] =  GetMessage("WIZ_MENU_LEFT");
		
	SetMenuTypes($arMenuTypes, WIZARD_SITE_ID);
?>