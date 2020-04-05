<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

	CModule::IncludeModule('fileman');
	$arMenuTypes = GetMenuTypes(WIZARD_SITE_ID);
	if($arMenuTypes['left'] && $arMenuTypes['left'] == GetMessage("WIZ_MENU_LEFT_DEFAULT"))
		$arMenuTypes['left'] =  GetMessage("WIZ_MENU_LEFT");
	if(!$arMenuTypes['leftfirst'])
		$arMenuTypes['leftfirst'] = GetMessage("WIZ_MENU_LEFT_FIRST");
		
	SetMenuTypes($arMenuTypes, WIZARD_SITE_ID);
	COption::SetOptionInt("fileman", "num_menu_param", 2, false ,WIZARD_SITE_ID);

?>