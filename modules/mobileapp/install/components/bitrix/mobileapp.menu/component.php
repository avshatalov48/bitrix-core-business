<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError(GetMessage('MOBILEAPP_NOT_INSTALLED'));
	return;
}

$menuBuildParams = array(
			'MENU_FILE' => $arParams['MENU_FILE_PATH'],
			'EVENT_NAME' => $arParams['BUILD_MENU_EVENT_NAME'],
	);

$arResult['MENU'] = CAdminMobileMenu::buildMenu($menuBuildParams);

if(!is_array($arResult['MENU']) || empty($arResult['MENU']))
	return;

$arResult['MENU_TITLE'] = $arParams['MENU_TITLE'];

if(isset($arParams['SYNC_REQUEST_PATH']))
	$arResult['LOGOUT_REQUEST_URL'] = $arParams['SYNC_REQUEST_PATH'].'?mobile_action=logout';
else
	$arResult['SYNC_REQUEST_PATH'] = false;

$arResult["AJAX_URL"] = $componentPath."/ajax.php";

CModule::IncludeModule('pull');
CJSCore::Init(array('ajax', 'pull'));

$this->IncludeComponentTemplate();
?>