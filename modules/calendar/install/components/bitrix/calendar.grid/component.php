<?php
use Bitrix\Calendar\Integration\SocialNetwork\Context\Context;
use Bitrix\Intranet\Settings\Tools\ToolsManager;


if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

if (!CModule::IncludeModule("calendar"))
{
	return ShowError("EC_CALENDAR_MODULE_NOT_INSTALLED");
}

CModule::IncludeModule("socialnetwork");
$APPLICATION->ResetException();
$APPLICATION->SetPageProperty("BodyClass", trim($APPLICATION->GetPageProperty("BodyClass")." no-all-paddings"));

$arParams['FILTER_ID'] = "CALENDAR_GRID_FILTER_".$arParams["CALENDAR_TYPE"]."_". ($arParams["OWNER_ID"] ?? '') ."_".CCalendar::GetCurUserId();

$viewTaskPath = '';
$editTaskPath = '';
if ($arParams['CALENDAR_TYPE'] === 'user')
{
	$viewTaskPath = str_replace(array('#user_id#', '#action#'), array($arParams["OWNER_ID"], 'view'), $arParams['PATH_TO_USER_TASK']);
	$editTaskPath = str_replace(array('#user_id#', '#action#', '#task_id#'), array($arParams["OWNER_ID"], 'edit', 0), $arParams['PATH_TO_USER_TASK']);
}
else if ($arParams['CALENDAR_TYPE'] === 'group')
{
	$viewTaskPath = str_replace(array('#group_id#', '#action#'), array($arParams["OWNER_ID"], 'view'), $arParams['PATH_TO_GROUP_TASK']);
	$editTaskPath = str_replace(array('#group_id#', '#action#', '#task_id#'), array($arParams["OWNER_ID"], 'edit', 0), $arParams['PATH_TO_GROUP_TASK']);
}

$arParams['USER_ID'] = CCalendar::GetCurUserId();
$arParams['SHOW_FILTER'] =
	($arParams['CALENDAR_TYPE'] === 'user' && (int)$arParams['OWNER_ID'] === (int)$arParams['USER_ID'])
	|| $arParams['CALENDAR_TYPE'] === 'company_calendar'
	|| $arParams['CALENDAR_TYPE'] === 'calendar_company'
	|| $arParams['CALENDAR_TYPE'] === 'company'
	|| $arParams['CALENDAR_TYPE'] === 'group'
;
$arParams['FILTER_ID'] = \Bitrix\Calendar\Ui\CalendarFilter::getFilterId(
	$arParams['CALENDAR_TYPE'],
	$arParams['OWNER_ID'] ?? null,
	$arParams['USER_ID']
);
$arParams['FILTER'] = \Bitrix\Calendar\Ui\CalendarFilter::getFilters();
$arParams['FILTER_PRESETS'] = \Bitrix\Calendar\Ui\CalendarFilter::getPresets($arParams['CALENDAR_TYPE']);

$params = [
	'type' => $arParams['CALENDAR_TYPE'],
	'ownerId' => $arParams['OWNER_ID'] ?? null,
	'pageUrl' => htmlspecialcharsback(POST_FORM_ACTION_URI),
	'allowSuperpose' => ($arParams['ALLOW_SUPERPOSE'] ?? null) === 'Y',
	'allowResMeeting' => ($arParams['ALLOW_RES_MEETING'] ?? null) !== 'N',
	'allowVideoMeeting' => ($arParams['ALLOW_RES_MEETING'] ?? null) !== 'N',
	'SectionControlsDOMId' => 'sidebar',
	'user_name_template' => empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]),
	'viewTaskPath' => $viewTaskPath,
	'editTaskPath' => $editTaskPath
];

if (isset($arParams["SIDEBAR_DOM_ID"]))
{
	$params['SectionControlsDOMId'] = $arParams["SIDEBAR_DOM_ID"];
}

// Create new instance of Event Calendar object
$EC = new CCalendar;
$EC->Init($params);
$arResult['ID'] = $EC->GetId();
$arResult['CALENDAR'] = $EC;

$arResult['CONTEXT'] = $arParams['CONTEXT'] ?? Context::getDefault();

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if (isset($request['action']) && $request['action'] === 'export')
{
	$arResult['IFRAME'] = $request['IFRAME'] === 'Y';
	CCalendarRequest::Process($EC, $request['action']);
}
else
{
	$arResult['IS_TOOL_AVAILABLE'] = !CModule::IncludeModule('intranet')
		|| ToolsManager::getInstance()->checkAvailabilityByToolId('calendar')
	;
	$this->IncludeComponentTemplate();
}
?>