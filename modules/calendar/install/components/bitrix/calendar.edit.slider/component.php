<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */

if (!CModule::IncludeModule('calendar'))
{
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("EC_CALENDAR_MODULE_NOT_INSTALLED"));
	return false;
}

$arResult['TIMEZONE_LIST'] = CCalendar::GetTimezoneList();
$arResult['FORM_USER_SETTINGS'] = \Bitrix\Calendar\UserSettings::getFormSettings(
	formType: $arParams['formType'],
	entryType: $arParams['type'],
);

$this->IncludeComponentTemplate();
?>