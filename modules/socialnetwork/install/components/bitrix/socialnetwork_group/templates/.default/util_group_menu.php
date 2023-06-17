<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $pageId */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$arReturnGroupMenu = $APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group_menu",
	"",
	[
		"GROUP_VAR" => ($arResult["ALIASES"]["group_id"] ?? 0),
		"PAGE_VAR" => ($arResult["ALIASES"]["page"] ?? ''),
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_GENERAL" => $arResult["PATH_TO_GROUP_GENERAL"],
		"PATH_TO_GROUP_MODS" => $arResult["PATH_TO_GROUP_MODS"],
		"PATH_TO_GROUP_USERS" => $arResult["PATH_TO_GROUP_USERS"],
		"PATH_TO_GROUP_EDIT" => $arResult["PATH_TO_GROUP_EDIT"],
		"PATH_TO_GROUP_REQUEST_SEARCH" => $arResult["PATH_TO_GROUP_REQUEST_SEARCH"],
		"PATH_TO_GROUP_COPY" => $arResult["PATH_TO_GROUP_COPY"],
		"PATH_TO_GROUP_REQUESTS" => $arResult["PATH_TO_GROUP_REQUESTS"],
		"PATH_TO_GROUP_REQUESTS_OUT" => $arResult["PATH_TO_GROUP_REQUESTS_OUT"],
		"PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
		"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
		"PATH_TO_GROUP_PHOTO" => $arResult["PATH_TO_GROUP_PHOTO"],
		"PATH_TO_GROUP_FORUM" => $arResult["PATH_TO_GROUP_FORUM"],
		"PATH_TO_GROUP_CALENDAR" => $arResult["PATH_TO_GROUP_CALENDAR"],
		"PATH_TO_GROUP_FILES" => $arResult["PATH_TO_GROUP_FILES"],
		"PATH_TO_GROUP_DISK" => $arResult["PATH_TO_GROUP_DISK"],
		"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
		"PATH_TO_SCRUM_TEAM_SPEED" => $arResult["PATH_TO_SCRUM_TEAM_SPEED"],
		"PATH_TO_SCRUM_BURN_DOWN" => $arResult["PATH_TO_SCRUM_BURN_DOWN"],
		"PATH_TO_GROUP_TASKS_TASK" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
		"PATH_TO_GROUP_CONTENT_SEARCH" => $arResult["PATH_TO_GROUP_CONTENT_SEARCH"],
		"FILES_GROUP_IBLOCK_ID" => $arParams["FILES_GROUP_IBLOCK_ID"],
		"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"PAGE_ID" => $pageId,
		'componentPage' => $arResult['componentPage'],
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => ($arParams["MAIN_MENU_TYPE"] ?? 'left'),
	],
	$component,
	[ 'HIDE_ICONS' => 'Y' ]
);

if (
	$USER->IsAuthorized()
	&& COption::GetOptionString("main", "wizard_solution", "", SITE_ID) === "community"
)
{
	include("util_community.php");
}

$APPLICATION->IncludeComponent(
	'bitrix:socialnetwork.admin.set',
	'',
	[],
	$component,
	[ 'HIDE_ICONS' => 'Y' ]
);
