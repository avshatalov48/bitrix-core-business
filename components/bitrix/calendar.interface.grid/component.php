<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("calendar"))
	return ShowError("EC_CALENDAR_MODULE_NOT_INSTALLED");

CModule::IncludeModule("socialnetwork");

$arParams["USER_ID"] = CCalendar::GetCurUserId();
$arParams["SHOW_TOP_VIEW_SWITCHER"] = isset($arParams["SHOW_TOP_VIEW_SWITCHER"]) ? $arParams["SHOW_TOP_VIEW_SWITCHER"] : true;
$arParams["SHOW_SECTION_SELECTOR"] = isset($arParams["SHOW_SECTION_SELECTOR"]) ? $arParams["SHOW_SECTION_SELECTOR"] : true;

$this->IncludeComponentTemplate();
?>