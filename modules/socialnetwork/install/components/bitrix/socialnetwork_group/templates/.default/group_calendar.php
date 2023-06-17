<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */

/** @global CMain $APPLICATION */

use Bitrix\Main\Application;

$pageId = "group_calendar";

include("util_group_menu.php");
include("util_group_profile.php");

$ownerId = $arResult["VARIABLES"]["group_id"];
if (
	!empty($arResult['groupFields'])
	&& \CSocNetFeatures::isActiveFeature(SONET_ENTITY_GROUP, $ownerId, 'calendar')
)
{
	$calendar2 = (
		(
			!IsModuleInstalled("intranet")
			|| COption::GetOptionString("intranet", "calendar_2", "N") === "Y"
		)
		&& CModule::IncludeModule("calendar")
	);
	if ($calendar2)
	{
		$APPLICATION->IncludeComponent(
			"bitrix:ui.sidepanel.wrapper",
			"",
			[
				'POPUP_COMPONENT_NAME' => "bitrix:calendar.grid",
				"POPUP_COMPONENT_TEMPLATE_NAME" => "",
				"POPUP_COMPONENT_PARAMS" => [
					"CALENDAR_TYPE" => 'group',
					"OWNER_ID" => $ownerId,
					"ALLOW_SUPERPOSE" => $arParams['CALENDAR_ALLOW_SUPERPOSE'],
					"ALLOW_RES_MEETING" => $arParams["CALENDAR_ALLOW_RES_MEETING"],
					"SET_TITLE" => 'Y',
					"SET_NAV_CHAIN" => 'Y',
					'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
					'PATH_TO_USER' => $arParams['PATH_TO_USER'],
					'PATH_TO_COMPANY_DEPARTMENT' => $arParams['PATH_TO_CONPANY_DEPARTMENT'],
					'HIDE_OWNER_IN_TITLE' => $arParams['HIDE_OWNER_IN_TITLE'],
					'PATH_TO_USER_TASK' => $arResult["PATH_TO_USER_TASKS_TASK"] ?? '',
					'PATH_TO_GROUP_TASK' => $arResult["PATH_TO_GROUP_TASKS_TASK"],
				],
				"POPUP_COMPONENT_PARENT" => $component,
				'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
				'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_TYPE' => 'SONET_GROUP',
				'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_ID' => $arResult['VARIABLES']['group_id'],
				'USE_PADDING' => false,
				'USE_UI_TOOLBAR' => 'Y',
				'UI_TOOLBAR_FAVORITES_TITLE_TEMPLATE' => (isset($arParams['HIDE_OWNER_IN_TITLE'])
				&& $arParams['HIDE_OWNER_IN_TITLE'] === 'Y' ? $arResult['PAGES_TITLE_TEMPLATE'] : ''),
			]
		);

		$APPLICATION->SetPageProperty('FavoriteTitleTemplate',
			(isset($arParams['HIDE_OWNER_IN_TITLE']) && $arParams['HIDE_OWNER_IN_TITLE'] === 'Y'
				? $arResult['PAGES_TITLE_TEMPLATE'] : ''));
	}
	else
	{
		$APPLICATION->IncludeComponent(
			"bitrix:ui.sidepanel.wrapper",
			"",
			[
				'POPUP_COMPONENT_NAME' => "bitrix:intranet.event_calendar",
				"POPUP_COMPONENT_TEMPLATE_NAME" => "",
				"POPUP_COMPONENT_PARAMS" => [
					"IBLOCK_TYPE" => $arParams['CALENDAR_IBLOCK_TYPE'],
					"IBLOCK_ID" => $arParams['CALENDAR_GROUP_IBLOCK_ID'],
					"OWNER_ID" => $ownerId,
					"OWNER_TYPE" => 'GROUP', // 'USER', 'GROUP' or 'NONE' for standart mode
					"MULTIPLE_MODE" => 'Y', // multiple calendars
					"INIT_DATE" => "",
					"WEEK_HOLIDAYS" => $arParams['CALENDAR_WEEK_HOLIDAYS'],
					"YEAR_HOLIDAYS" => $arParams['CALENDAR_YEAR_HOLIDAYS'],
					"LOAD_MODE" => "ajax",
					"USE_DIFFERENT_COLORS" => "Y",
					"EVENT_COLORS" => "",
					"ADVANCED_MODE_SETTINGS" => "Y",
					"SET_TITLE" => 'Y',
					"SET_NAV_CHAIN" => 'Y',
					"WORK_TIME_START" => $arParams['CALENDAR_WORK_TIME_START'],
					"WORK_TIME_END" => $arParams['CALENDAR_WORK_TIME_END'],
					"PATH_TO_USER" => $arParams["PATH_TO_USER"],
					"PATH_TO_USER_CALENDAR" => $arResult["PATH_TO_USER_CALENDAR"],
					"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
					"PATH_TO_GROUP_CALENDAR" => $arResult["PATH_TO_GROUP_CALENDAR"],
					"ALLOW_SUPERPOSE" => $arParams['CALENDAR_ALLOW_SUPERPOSE'],
					"SUPERPOSE_GROUPS_CALS" => $arParams['CALENDAR_SUPERPOSE_GROUPS_CALS'],
					"SUPERPOSE_USERS_CALS" => $arParams['CALENDAR_SUPERPOSE_USERS_CALS'],
					"SUPERPOSE_CUR_USER_CALS" => $arParams['CALENDAR_SUPERPOSE_CUR_USER_CALS'],
					"SUPERPOSE_CAL_IDS" => $arParams['CALENDAR_SUPERPOSE_CAL_IDS'],
					"SUPERPOSE_GROUPS_IBLOCK_ID" => $arParams['CALENDAR_GROUP_IBLOCK_ID'],
					"SUPERPOSE_USERS_IBLOCK_ID" => $arParams['CALENDAR_USER_IBLOCK_ID'],
					"USERS_IBLOCK_ID" => $arParams['CALENDAR_USER_IBLOCK_ID'],
					"ALLOW_RES_MEETING" => $arParams["CALENDAR_ALLOW_RES_MEETING"],
					"RES_MEETING_IBLOCK_ID" => $arParams["CALENDAR_RES_MEETING_IBLOCK_ID"],
					"PATH_TO_RES_MEETING" => $arParams["CALENDAR_PATH_TO_RES_MEETING"],
					"RES_MEETING_USERGROUPS" => $arParams["CALENDAR_RES_MEETING_USERGROUPS"],
					"REINVITE_PARAMS_LIST" => $arParams["CALENDAR_REINVITE_PARAMS_LIST"],
					"ALLOW_VIDEO_MEETING" => $arParams["CALENDAR_ALLOW_VIDEO_MEETING"],
					"VIDEO_MEETING_IBLOCK_ID" => $arParams["CALENDAR_VIDEO_MEETING_IBLOCK_ID"],
					"PATH_TO_VIDEO_MEETING" => $arParams["CALENDAR_PATH_TO_VIDEO_MEETING"],
					"PATH_TO_VIDEO_MEETING_DETAIL" => $arParams["CALENDAR_PATH_TO_VIDEO_MEETING_DETAIL"],
					"VIDEO_MEETING_USERGROUPS" => $arParams["CALENDAR_VIDEO_MEETING_USERGROUPS"],
				],
				"POPUP_COMPONENT_PARENT" => $component,
				'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
				'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_TYPE' => 'SONET_GROUP',
				'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_ID' => $arResult['VARIABLES']['group_id'],
				'USE_UI_TOOLBAR' => 'Y',
			]
		);
	}
}