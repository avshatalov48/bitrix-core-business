<?
if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_FOUND"));
	return;
}

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
$arParams["IFRAME_POPUP_VAR_NAME"] = array_key_exists("IFRAME_POPUP_VAR_NAME", $arParams) && $arParams["IFRAME_POPUP_VAR_NAME"] <> '' 
									? CUtil::JSEscape($arParams["IFRAME_POPUP_VAR_NAME"]) : "sonetGroupIFramePopup";

$this->IncludeComponentTemplate();
?>