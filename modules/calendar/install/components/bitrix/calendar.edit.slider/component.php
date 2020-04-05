<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("calendar"))
	return ShowError("EC_CALENDAR_MODULE_NOT_INSTALLED");

$arResult['TIMEZONE_LIST'] = CCalendar::GetTimezoneList();
$arResult['FORM_USER_SETTINGS'] = CCalendarUserSettings::getFormSettings($arParams['formType']);

$this->IncludeComponentTemplate();
?>