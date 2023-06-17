<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponent $component */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$pageId = "user_tasks";
$taskId = (int)$arResult['VARIABLES']['task_id'];
$userId = (int)$arResult['VARIABLES']['user_id'];
$action =
	$arResult['VARIABLES']['action'] === 'edit'
		? 'edit'
		: 'view'
;

if (Context::getCurrent()->getRequest()->get('IFRAME'))
{
	include("util_menu.php");
	include("util_profile.php");

	Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].$this->getFolder().'/result_modifier.php');

	if (
		!CSocNetFeatures::IsActiveFeature(
			SONET_ENTITY_USER,
			$userId,
			"tasks"
		)
	)
	{
		$pathToUserFeatures = str_replace(["#user_id#", "#USER_ID#"], $userId, $arResult['PATH_TO_USER_FEATURES']);
		$pathToUserFeaturesHref = '<a href="' . $pathToUserFeatures . '">';

		echo Loc::getMessage('SU_T_TASKS_UNAVAILABLE', [
			'#A_BEGIN#' => $pathToUserFeaturesHref,
			'#A_END#' => '</a>',
		]);
	}
	elseif (Loader::includeModule('tasks'))
	{
		$APPLICATION->IncludeComponent(
			"bitrix:tasks.iframe.popup",
			"wrap",
			[
				"ACTION" => $action,
				"FORM_PARAMETERS" => [
					"ID" => $taskId,
					"GROUP_ID" => "",
					"USER_ID" => $userId,
					"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
					"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
					"PATH_TO_GROUP_TASKS" => $arParams["PATH_TO_GROUP_TASKS"],
					"PATH_TO_GROUP_TASKS_TASK" => "",
					"PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
					"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
					"PATH_TO_USER_TASKS_PROJECTS_OVERVIEW" => $arResult["PATH_TO_USER_TASKS_PROJECTS_OVERVIEW"],
					"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
					"PATH_TO_USER_TEMPLATES_TEMPLATE" => $arResult["PATH_TO_USER_TEMPLATES_TEMPLATE"],
					"SET_NAVCHAIN" => $arResult["SET_NAV_CHAIN"],
					"SET_TITLE" => $arResult["SET_TITLE"],
					"SHOW_RATING" => $arParams["SHOW_RATING"],
					"RATING_TYPE" => $arParams["RATING_TYPE"],
					"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
				],
			],
			$component,
			["HIDE_ICONS" => "Y"]
		);
	}
}
else
{
	$userId = CurrentUser::get()->getId();
	$backgroundForTask = true;
	require_once('user_tasks_task_background.php');
}