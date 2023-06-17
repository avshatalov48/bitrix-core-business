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

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Tasks\Internals\Routes\RouteDictionary;
use Bitrix\Tasks\Ui\Filter\Task;

$pageId = "group_tasks";
$groupId = (int)$arResult['VARIABLES']['group_id'];

include("util_group_menu.php");
include("util_group_profile.php");

if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $groupId, "tasks"))
{
	include("util_copy_tasks.php");

	$sprintId = -1;
	$kanbanIsTimelineMode = 'N';
	$isPersonalKanban = 'N';

	if (class_exists(Task::class))
	{
		Task::setGroupId($groupId);
		$state = Task::listStateInit()->getState();

		switch ($state['VIEW_SELECTED']['CODENAME'])
		{
			case 'VIEW_MODE_GANTT':
				$componentName = 'bitrix:tasks.task.gantt';
				break;
			case 'VIEW_MODE_PLAN':
				$componentName = 'bitrix:tasks.kanban';
				$isPersonalKanban = 'Y';
				break;
			case 'VIEW_MODE_KANBAN':
				$componentName = 'bitrix:tasks.kanban';
				break;
			case 'VIEW_MODE_TIMELINE':
				$componentName = 'bitrix:tasks.kanban';
				$kanbanIsTimelineMode = 'Y';
				$isPersonalKanban = 'Y';
				break;
			case 'VIEW_MODE_SPRINT':
				$componentName = 'bitrix:tasks.kanban';
				$sprintId = 0;
				break;
			case 'VIEW_MODE_CALENDAR':
				$componentName = 'bitrix:tasks.task.calendar';
				break;

			default:
				$componentName = 'bitrix:tasks.task.list';
				break;
		}
	}
	else
	{
		$componentName = 'bitrix:tasks.list';
	}

	$group = Bitrix\Socialnetwork\Item\Workgroup::getById($groupId);
	if ($group && $group->isScrumProject())
	{
		$componentName = 'bitrix:tasks.scrum';
	}

	$componentParams = [
		"INCLUDE_INTERFACE_HEADER" => "Y",
		"PERSONAL" => $isPersonalKanban,
		"TIMELINE_MODE" => $kanbanIsTimelineMode,
		"KANBAN_SHOW_VIEW_MODE"=>'Y',
		"SPRINT_ID" => $sprintId,
		"GROUP_ID" => $groupId,
		"ITEMS_COUNT" => "50",
		"PAGE_VAR" => ($arResult["ALIASES"]["page"] ?? ''),
		"GROUP_VAR" => ($arResult["ALIASES"]["group_id"] ?? ''),
		"VIEW_VAR" => ($arResult["ALIASES"]["view_id"] ?? ''),
		"TASK_VAR" => ($arResult["ALIASES"]["task_id"] ?? ''),
		"ACTION_VAR" => ($arResult["ALIASES"]["action"] ?? ''),
		"PATH_TO_USER_TASKS_TEMPLATES" => $arParams["PATH_TO_USER_TASKS_TEMPLATES"],
		"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
		"PATH_TO_SCRUM_TEAM_SPEED" => $arResult["PATH_TO_SCRUM_TEAM_SPEED"],
		"PATH_TO_SCRUM_BURN_DOWN" => $arResult["PATH_TO_SCRUM_BURN_DOWN"],
		"PATH_TO_GROUP_TASKS_TASK" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
		"PATH_TO_GROUP_TASKS_VIEW" => $arResult["PATH_TO_GROUP_TASKS_VIEW"],
		"PATH_TO_GROUP_TASKS_REPORT" => $arResult["PATH_TO_GROUP_TASKS_REPORT"],
		"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
		"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"FORUM_ID" => $arParams["TASK_FORUM_ID"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
		"SHOW_YEAR" => ($arParams["SHOW_YEAR"] ?? ''),
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"USE_THUMBNAIL_LIST" => "N",
		"INLINE" => "Y",
		"HIDE_OWNER_IN_TITLE" => $arParams['HIDE_OWNER_IN_TITLE'],
		"TASKS_ALWAYS_EXPANDED" => 'Y',
		'LAZY_LOAD' => 'Y',
	];

	$APPLICATION->IncludeComponent(
		"bitrix:ui.sidepanel.wrapper",
		"",
		[
			'POPUP_COMPONENT_NAME' => $componentName,
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" => $componentParams,
			"POPUP_COMPONENT_PARENT" => $component,
			'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
			'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_TYPE' => 'SONET_GROUP',
			'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_ID' => $groupId,
			'USE_PADDING' => !($group && $group->isScrumProject()),
			'USE_UI_TOOLBAR' => 'Y',
			'UI_TOOLBAR_FAVORITES_TITLE_TEMPLATE' => $arResult['PAGES_TITLE_TEMPLATE'],
		]
	);

	$APPLICATION->SetPageProperty('FavoriteTitleTemplate', $arResult['PAGES_TITLE_TEMPLATE']);
}