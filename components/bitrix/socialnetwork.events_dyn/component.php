<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (IsModuleInstalled("im"))
{
	if ($this->__templateName == "popup")
		$this->__templateName = ".default";

	$this->IncludeComponentTemplate();
	return false;
}

$arParams["USER_ID"] = intval($arParams["USER_ID"]);
if ($arParams["USER_ID"] <= 0)
	$arParams["USER_ID"] = intval($USER->GetID());

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if ($arParams["MESSAGE_VAR"] == '')
	$arParams["MESSAGE_VAR"] = "message_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGE_FORM"] = trim($arParams["PATH_TO_MESSAGE_FORM"]);
if ($arParams["PATH_TO_MESSAGE_FORM"] == '')
	$arParams["PATH_TO_MESSAGE_FORM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_form&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGE_FORM_MESS"] = trim($arParams["PATH_TO_MESSAGE_FORM_MESS"]);
if ($arParams["PATH_TO_MESSAGE_FORM_MESS"] == '')
	$arParams["PATH_TO_MESSAGE_FORM_MESS"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_form_mess&".$arParams["USER_VAR"]."=#user_id#&".$arParams["MESSAGE_VAR"]."=#message_id#";

$arParams["PATH_TO_MESSAGES_CHAT"] = trim($arParams["PATH_TO_MESSAGES_CHAT"]);
if ($arParams["PATH_TO_MESSAGES_CHAT"] == '')
	$arParams["PATH_TO_MESSAGES_CHAT"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_chat&".$arParams["USER_VAR"]."=#user_id#";

$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);

$arParams["UNREAD_CNT_STR_BEFORE"] = trim($arParams["UNREAD_CNT_STR_BEFORE"]);
if ($arParams["UNREAD_CNT_STR_BEFORE"] == '')
	$arParams["UNREAD_CNT_STR_BEFORE"] = "";

$arParams["UNREAD_CNT_STR_AFTER"] = trim($arParams["UNREAD_CNT_STR_AFTER"]);
if ($arParams["UNREAD_CNT_STR_AFTER"] == '')
	$arParams["UNREAD_CNT_STR_AFTER"] = "";

$arParams["AJAX_LONG_TIMEOUT"] = intval(trim($arParams["AJAX_LONG_TIMEOUT"]));
if ($arParams["AJAX_LONG_TIMEOUT"] <= 0)
	$arParams["AJAX_LONG_TIMEOUT"] = 60;

if (trim($arParams["NAME_TEMPLATE"]) == '')
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();

$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";
$arParams['POPUP'] = $arParams['POPUP'] != "Y" ? "N" : "Y";

if (IsModuleInstalled('intranet') && !array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = $arParams["~PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";

$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

if ($GLOBALS["USER"]->IsAuthorized())
{
	// works only for old templates
	$dirPath = '/bitrix/components/bitrix/socialnetwork.events_dyn';
	$arResult["MsgGetPath"] = $dirPath."/get_message.php";
	$arResult["MsgSetPath"] = $dirPath."/set_message.php";

	if (COption::GetOptionString("socialnetwork", "allow_tooltip", "Y") != "Y")
		$arResult["USE_TOOLTIP"] = false;

	$this->IncludeComponentTemplate();

	return Array("arResult" => $arResult, "arParams" => $arParams);
}
?>