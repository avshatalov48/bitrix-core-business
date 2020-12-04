<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arResult['NAME'] = $arParams['PARAMS']['NAME'];
$arResult['DATE_FROM'] = $arParams['PARAMS']['DATE_FROM'];
$arResult['LOCATION'] = $arParams['PARAMS']['LOCATION'];
$arResult['ATTENDEES_LIST'] = $arParams['PARAMS']['ATTENDEES'];
$arResult['ORGANIZER'] = $arParams['PARAMS']['ORGANIZER'];
$arResult['DESCRIPTION'] = htmlspecialcharsback($arParams['PARAMS']['DESCRIPTION']);
$arResult['FILES'] = $arParams['PARAMS']['FILES_LINK'];
$arResult['CHANGE_FIELDS'] = explode(';', $arParams['PARAMS']['CHANGE_FIELDS']);

$ex = in_array(["DATE_FROM", "RRULE"], $arResult['CHANGE_FIELDS']);

switch ($arParams['PARAMS']['METHOD'])
{
	case 'request':
		$arResult['TITLE'] = GetMessage("EC_CALENDAR_ICAL_MAIL_METHOD_REQUEST");
		break;
	case 'edit':
//		$arResult['TITLE'] = GetMessage("EC_CALENDAR_ICAL_MAIL_METHOD_EDIT");
		$arResult['TITLE'] = $arParams['PARAMS']['TITLE'];
		break;
	case 'cancel':
		$arResult['TITLE'] = GetMessage("EC_CALENDAR_ICAL_MAIL_METHOD_CANCEL");
		break;
	case 'reply':
		$arResult['TITLE'] = $arParams['PARAMS']['ANSWER'] === 'accepted'
			? GetMessage("EC_CALENDAR_ICAL_MAIL_METHOD_REPLY_ACCEPTED")
			: GetMessage("EC_CALENDAR_ICAL_MAIL_METHOD_REPLY_DECLINED");
		break;
}

$this->IncludeComponentTemplate();