<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arResult = array();

if (CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false))
	$arResult["IS_SESSION_ADMIN"] = $arResult["SHOW_BANNER"] = isset($_SESSION["SONET_ADMIN"]);

$this->IncludeComponentTemplate();
?>