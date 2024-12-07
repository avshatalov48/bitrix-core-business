<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "group_card";

include("util_group_menu.php");
include("util_group_limit.php");

$componentParameters = [
	"PATH_TO_USER" => $arParams["PATH_TO_USER"],
	"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
	"PATH_TO_GROUP_EDIT" => $arResult["PATH_TO_GROUP_EDIT"],
	"PATH_TO_GROUP_INVITE" => $arResult["PATH_TO_GROUP_INVITE"],
	"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
	"PATH_TO_GROUP_COPY" => $arResult["PATH_TO_GROUP_COPY"],
	"PATH_TO_GROUP_REQUEST_SEARCH" => $arResult["PATH_TO_GROUP_REQUEST_SEARCH"],
	"PATH_TO_USER_REQUEST_GROUP" => $arResult["PATH_TO_USER_REQUEST_GROUP"],
	"PATH_TO_GROUP_REQUESTS" => $arResult["PATH_TO_GROUP_REQUESTS"],
	"PATH_TO_GROUP_REQUESTS_OUT" => $arResult["PATH_TO_GROUP_REQUESTS_OUT"],
	"PATH_TO_GROUP_MODS" => $arResult["PATH_TO_GROUP_MODS"],
	"PATH_TO_GROUP_USERS" => $arResult["PATH_TO_GROUP_USERS"],
	"PATH_TO_USER_LEAVE_GROUP" => $arResult["PATH_TO_USER_LEAVE_GROUP"],
	"PATH_TO_GROUP_DELETE" => $arResult["PATH_TO_GROUP_DELETE"],
	"PATH_TO_GROUP_FEATURES" => $arResult["PATH_TO_GROUP_FEATURES"],
	"PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
	"PATH_TO_SEARCH" => $arResult["PATH_TO_SEARCH"],
	"PATH_TO_SEARCH_TAG" => $arParams["PATH_TO_SEARCH_TAG"],
	"PAGE_VAR" => $arResult["ALIASES"]["page"] ?? '',
	"USER_VAR" => $arResult["ALIASES"]["user_id"] ?? '',
	"GROUP_VAR" => $arResult["ALIASES"]["group_id"] ?? '',
	"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
	"SET_TITLE" => $arResult["SET_TITLE"],
	"USER_ID" => $arResult["VARIABLES"]["user_id"] ?? 0,
	"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	"ITEMS_COUNT" => $arParams["ITEM_MAIN_COUNT"],
	"PATH_TO_GROUP_BLOG_POST" => $arResult["PATH_TO_GROUP_BLOG_POST"],
	"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
	"PATH_TO_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
	"PATH_TO_POST" => $arParams["PATH_TO_USER_BLOG_POST"],
	"PATH_TO_POST_EDIT" => $arParams["PATH_TO_USER_BLOG_POST_EDIT"] ?? '',
	"PATH_TO_USER_BLOG_POST_IMPORTANT" => $arResult["PATH_TO_USER_BLOG_POST_IMPORTANT"],
	"PATH_TO_GROUP_FORUM" => $arResult["PATH_TO_GROUP_FORUM"],
	"PATH_TO_GROUP_FORUM_TOPIC" => $arResult["~PATH_TO_GROUP_FORUM_TOPIC"] ?? '',
	"PATH_TO_GROUP_FORUM_MESSAGE" => $arResult["~PATH_TO_GROUP_FORUM_MESSAGE"] ?? '',
	"FORUM_ID" => $arParams["FORUM_ID"],
	"PATH_TO_GROUP_SUBSCRIBE" => $arResult["PATH_TO_GROUP_SUBSCRIBE"],
	"PATH_TO_MESSAGE_TO_GROUP" => $arResult["PATH_TO_MESSAGE_TO_GROUP"],
	"BLOG_GROUP_ID" => $arParams["BLOG_GROUP_ID"],
	"TASK_VAR" => $arResult["ALIASES"]["task_id"] ?? '',
	"TASK_ACTION_VAR" => $arResult["ALIASES"]["action"] ?? '',
	"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
	"PATH_TO_GROUP_TASKS_TASK" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
	"PATH_TO_GROUP_TASKS_VIEW" => $arResult["PATH_TO_GROUP_TASKS_VIEW"],
	"PATH_TO_GROUP_CONTENT_SEARCH" => $arResult["PATH_TO_GROUP_CONTENT_SEARCH"],
	"TASK_FORUM_ID" => $arParams["TASK_FORUM_ID"],
	"THUMBNAIL_LIST_SIZE" => 30,
	"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
	"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
	"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
	"SHOW_YEAR" => $arParams["SHOW_YEAR"] ?? '',
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
	"CAN_OWNER_EDIT_DESKTOP" => $arParams["CAN_OWNER_EDIT_DESKTOP"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
	"SEARCH_TAGS_PAGE_ELEMENTS" => $arParams["SEARCH_TAGS_PAGE_ELEMENTS"],
	"SEARCH_TAGS_PERIOD" => $arParams["SEARCH_TAGS_PERIOD"],
	"SEARCH_TAGS_FONT_MAX" => $arParams["SEARCH_TAGS_FONT_MAX"],
	"SEARCH_TAGS_FONT_MIN" => $arParams["SEARCH_TAGS_FONT_MIN"],
	"SEARCH_TAGS_COLOR_NEW" => $arParams["SEARCH_TAGS_COLOR_NEW"],
	"SEARCH_TAGS_COLOR_OLD" => $arParams["SEARCH_TAGS_COLOR_OLD"],
	"PATH_TO_USER_LOG" => $arParams["~PATH_TO_USER_LOG"],
	"PATH_TO_GROUP_LOG" => $arResult["PATH_TO_GROUP_LOG"],
	"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
	"LOG_SUBSCRIBE_ONLY" => $arParams["LOG_SUBSCRIBE_ONLY"],
	"GROUP_PROPERTY" => $arResult["GROUP_PROPERTY"],
	"GROUP_USE_BAN" => $arParams["GROUP_USE_BAN"],
	"BLOG_ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"],
	"SHOW_RATING" => $arParams["SHOW_RATING"],
	"LOG_THUMBNAIL_SIZE" => $arParams["LOG_THUMBNAIL_SIZE"],
	"LOG_COMMENT_THUMBNAIL_SIZE" => $arParams["LOG_COMMENT_THUMBNAIL_SIZE"],
	"LOG_NEW_TEMPLATE" => $arParams["LOG_NEW_TEMPLATE"],
];

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'USE_PADDING' => false,
		'POPUP_COMPONENT_NAME' => 'bitrix:socialnetwork.group',
		'POPUP_COMPONENT_TEMPLATE_NAME' => 'card',
		'POPUP_COMPONENT_PARAMS' => $componentParameters,
		'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_TYPE' => 'SONET_GROUP',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_ID' => $arResult['VARIABLES']['group_id'],
		'POPUP_COMPONENT_BITRIX24_THEME_BEHAVIOUR' => 'return',
		'USE_UI_TOOLBAR' => 'Y',
	]
);