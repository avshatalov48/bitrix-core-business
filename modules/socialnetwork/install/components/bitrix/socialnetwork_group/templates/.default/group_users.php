<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "group_users";
include("util_group_menu.php");
include("util_group_profile.php");
include("util_group_limit.php");

$componentParameters = [
	"PATH_TO_USER" => $arParams["PATH_TO_USER"],
	"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
	"PATH_TO_GROUP_EDIT" => $arResult["PATH_TO_GROUP_EDIT"],
	"PATH_TO_GROUP_INVITE" => $arResult["PATH_TO_GROUP_INVITE"],
	"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
	"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
	"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
	"PAGE_VAR" => $arResult["ALIASES"]["page"] ?? '',
	"GROUP_VAR" => $arResult["ALIASES"]["group_id"] ?? 0,
	"USER_VAR" => $arResult["ALIASES"]["user_id"] ?? 0,
	"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
	"SET_TITLE" => $arResult["SET_TITLE"],
	'GROUP_ID' => $arResult['VARIABLES']['group_id'],
	"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
	"THUMBNAIL_LIST_SIZE" => 42,
	"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
	"SHOW_YEAR" => $arParams["SHOW_YEAR"] ?? '',
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"GROUP_USE_BAN" => $arParams["GROUP_USE_BAN"],
	"USE_AUTO_MEMBERS" => "Y",
	'MODE' => 'MEMBERS',
	'FILTER_ID' => 'SOCIALNETWORK_WORKGROUP_USER_LIST',
];

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:socialnetwork.group.user.list',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => $componentParameters,
		'USE_UI_TOOLBAR' => 'Y',
	]
);