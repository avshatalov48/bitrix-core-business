<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var array $tagSliderParams */
/** @var CBitrixComponent $component */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Tasks\Ui\Filter\Task;
use Bitrix\Main\Loader;

$pageId = "user_tasks";
include("util_menu.php");
include("util_profile.php");

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].$this->getFolder().'/result_modifier.php');

$userId = $arResult['VARIABLES']['user_id'];
if (
	!CSocNetFeatures::IsActiveFeature(
		SONET_ENTITY_USER,
		$userId,
		'tasks'
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
	Task::setUserId($userId);
	$state = Task::listStateInit()->getState();

	$kanbanIsTimelineMode = 'N';
	$isPersonalKanban = 'N';

	switch ($state[ 'VIEW_SELECTED' ][ 'CODENAME' ])
	{
		case 'VIEW_MODE_GANTT':
			$componentName = 'bitrix:tasks.task.gantt';
			break;
		case 'VIEW_MODE_PLAN':
			$componentName = 'bitrix:tasks.kanban';
			$isPersonalKanban = 'Y';
			break;
		case 'VIEW_MODE_TIMELINE':
			$componentName = 'bitrix:tasks.kanban';
			$kanbanIsTimelineMode = 'Y';
			$isPersonalKanban = 'Y';
			break;
		case 'VIEW_MODE_CALENDAR':
			$componentName = 'bitrix:tasks.task.calendar';
			break;
		default:
			Task::listStateInit()->setViewMode(\CTaskListState::VIEW_MODE_LIST);
			$componentName = 'bitrix:tasks.task.list';
			break;
	}

	$componentParams = [
		"INCLUDE_INTERFACE_HEADER" => "Y",
		"TIMELINE_MODE" => $kanbanIsTimelineMode,
		"PERSONAL" => $isPersonalKanban,
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"STATE" => [
			'ROLES' => $state['ROLES'],
			'SELECTED_ROLES' => $state['ROLES'],
			'VIEWS' => $state['VIEWS'],
			'SELECTED_VIEWS' => $state['VIEWS'],
		],
		"ITEMS_COUNT" => "50",
		"PAGE_VAR" => $arResult["ALIASES"]["page"] ?? '',
		"USER_VAR" => $userId,
		"VIEW_VAR" => $arResult["ALIASES"]["view_id"] ?? '',
		"TASK_VAR" => $arResult['ALIASES']['task_id'] ?? '',
		"ACTION_VAR" => $arResult["ALIASES"]["action"] ?? '',
		"PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
		"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
		"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
		"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
		"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
		"PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
		"PATH_TO_USER_TASKS_REPORT" => $arResult["PATH_TO_USER_TASKS_REPORT"],
		"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
		"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
		"PATH_TO_GROUP_TASKS" => $arParams["PATH_TO_GROUP_TASKS"],
		"PATH_TO_GROUP_TASKS_TASK" => $arParams["PATH_TO_GROUP_TASKS_TASK"],
		"PATH_TO_GROUP_TASKS_VIEW" => $arParams["PATH_TO_GROUP_TASKS_VIEW"],
		"PATH_TO_GROUP_TASKS_REPORT" => $arParams["PATH_TO_GROUP_TASKS_REPORT"] ?? '',
		'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"FORUM_ID" => $arParams["TASK_FORUM_ID"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
		"SHOW_YEAR" => $arParams["SHOW_YEAR"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"USE_THUMBNAIL_LIST" => "N",
		"INLINE" => "Y",
		"USE_PAGINATION" => 'Y',
		'HIDE_OWNER_IN_TITLE' => $arParams['HIDE_OWNER_IN_TITLE'],
		"PREORDER" => [
			'STATUS_COMPLETE' => 'asc'
		],
	];

	if (isset($tagSliderParams))
	{
		Task::listStateInit()->setViewMode(\CTaskListState::VIEW_MODE_LIST);
		$state = Task::listStateInit()->getState();
		$componentParams['STATE'] = $state;
		$componentParams = array_merge($componentParams, $tagSliderParams);
		$componentName = 'bitrix:tasks.task.list';
	}

	$APPLICATION->IncludeComponent(
		"bitrix:ui.sidepanel.wrapper",
		"",
		[
			'POPUP_COMPONENT_NAME' => $componentName,
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" => $componentParams,
			"POPUP_COMPONENT_PARENT" => $component,
		]
	);
}
?>