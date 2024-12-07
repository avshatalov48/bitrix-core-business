<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("calendar"))
{
	ShowError("EC_CALENDAR_MODULE_NOT_INSTALLED");
	return;
}

$arResult['TIMEZONE_LIST'] = CCalendar::GetTimezoneList();

$this->IncludeComponentTemplate();
