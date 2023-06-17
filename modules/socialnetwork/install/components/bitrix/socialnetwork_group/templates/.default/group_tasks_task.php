<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
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

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Socialnetwork\Internals\Registry\FeaturePermRegistry;

$userId = CurrentUser::get()->getId();
$pageId = "group_tasks";
$groupId = (int)$arResult['VARIABLES']['group_id'];
$taskId = (int)$arResult['VARIABLES']['task_id'];
$action =
	$arResult['VARIABLES']['action'] === 'edit'
		? 'edit'
		: 'view'
;

$formParams = [
	"ID" => $taskId,
	"GROUP_ID" => $groupId,
	"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
	"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
	"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
	"PATH_TO_GROUP_TASKS_TASK" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
	"PATH_TO_USER_TASKS_TEMPLATES" => $arParams["PATH_TO_USER_TASKS_TEMPLATES"],
	"PATH_TO_USER_TEMPLATES_TEMPLATE" => $arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"],
	"SET_NAVCHAIN" => $arResult["SET_NAV_CHAIN"],
	"SET_TITLE" => $arResult["SET_TITLE"],
	"SHOW_RATING" => $arParams["SHOW_RATING"],
	"RATING_TYPE" => $arParams["RATING_TYPE"],
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
];

if (Context::getCurrent()->getRequest()->get('IFRAME'))
{
	include("util_group_menu.php");
	include("util_group_profile.php");

	if (
		CSocNetFeatures::IsActiveFeature(
			SONET_ENTITY_GROUP,
			$groupId,
			'tasks'
		)
	)
	{
		$APPLICATION->IncludeComponent(
			"bitrix:tasks.iframe.popup",
			"wrap",
			[
				"ACTION" => $action,
				"FORM_PARAMETERS" => $formParams,
				'HIDE_MENU_PANEL' => 'Y',
			],
			$component,
			["HIDE_ICONS" => "Y"]
		);
	}
}
else
{
	$showPersonalTasks = false;
	if (
		!FeaturePermRegistry::getInstance()->get(
			$groupId,
			'tasks',
			'view',
			$userId
		)
		|| !FeaturePermRegistry::getInstance()->get(
			$groupId,
			'tasks',
			'view_all',
			$userId
		)
	)
	{
		$showPersonalTasks = true;
	}

	$backgroundForTask = true;
	require_once('group_tasks_task_background.php');
}