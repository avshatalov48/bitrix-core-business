<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

include("util_menu.php");
include("util_profile.php");

$pageId = 'user_tasks_projects';

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].$this->getFolder().'/result_modifier.php');

$userId = (int) $arResult["VARIABLES"]["user_id"];

$groupId = 0;
if (isset($arResult['VARIABLES']['group_id']) && (int) $arResult['VARIABLES']['group_id'])
{
	$groupId = (int) $arResult['VARIABLES']['group_id'];
}

if (Loader::includeModule("tasks")) {
	if (!$groupId) {
		$groupId = \Bitrix\Tasks\Integration\SocialNetwork\Group::getLastViewedProject($userId);
	} else {
		\Bitrix\Tasks\Integration\SocialNetwork\Group::setLastViewedProject($userId, $groupId);
	}
}

if (!$groupId || CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $groupId, "tasks"))
{
	if (Loader::includeModule("tasks"))
	{
		$APPLICATION->includeComponent(
			"bitrix:socialnetwork.copy.checker",
			"",
			[
				"moduleId" => \Bitrix\Tasks\Copy\Integration\Group::MODULE_ID,
				"queueId" => $groupId,
				"stepperClassName" => \Bitrix\Tasks\Copy\Integration\Group::STEPPER_CLASS,
				"checkerOption" => \Bitrix\Tasks\Copy\Integration\Group::CHECKER_OPTION,
				"errorOption" => \Bitrix\Tasks\Copy\Integration\Group::ERROR_OPTION,
				"titleMessage" => GetMessage("TASKS_STEPPER_PROGRESS_TITLE"),
				"errorMessage" => GetMessage("TASKS_STEPPER_PROGRESS_ERROR"),
			],
			$component,
			["HIDE_ICONS" => "Y"]
		);
	}

	if ($groupId && class_exists('Bitrix\Tasks\Ui\Filter\Task'))
	{
		\Bitrix\Tasks\Ui\Filter\Task::setGroupId($groupId);

		$state = \Bitrix\Tasks\Ui\Filter\Task::listStateInit();
		if (!array_key_exists('F_STATE', $_GET))
		{
			$state->setViewMode(CTaskListState::VIEW_MODE_KANBAN);
		}
		$state = $state->getState();

		$kanbanIsTimelineMode = 'N';
		$isPersonalKanban = 'N';

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

			case 'VIEW_MODE_CALENDAR':
				$componentName = 'bitrix:tasks.task.calendar';
				break;

			default:
				$componentName = 'bitrix:tasks.task.list';
				break;
		}
	}
	elseif (class_exists('Bitrix\Tasks\Ui\Filter\Task'))
	{
		$componentName = 'bitrix:tasks.kanban';
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

	$APPLICATION->IncludeComponent(
		$componentName,
		".default",
		[
			"INCLUDE_INTERFACE_HEADER" => 'Y',
			"MARK_SECTION_PROJECTS" => 'Y',
			"SHOW_SECTION_PROJECTS" => 'Y',
			"SHOW_SECTION_PROJECT" => 'Y',
			"PERSONAL" => $isPersonalKanban,
			"TIMELINE_MODE" => $kanbanIsTimelineMode,
			"KANBAN_SHOW_VIEW_MODE" => 'Y',
			"GROUP_ID" => $groupId,
			"PROJECT_VIEW" => 'Y',
			"USER_ID" => $userId,
			"STATE" => [
				'ROLES' => $state['ROLES'],
				'SELECTED_ROLES' => $state['ROLES'],
				'VIEWS' => $state['VIEWS'],
				'SELECTED_VIEWS' => $state['VIEWS'],
			],
			"ITEMS_COUNT" => "50",
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"USER_VAR" => $arResult["ALIASES"]["user_id"],
			"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
			"VIEW_VAR" => $arResult["ALIASES"]["view_id"],
			"TASK_VAR" => $arResult["ALIASES"]["task_id"],
			"ACTION_VAR" => $arResult["ALIASES"]["action"],
			"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
			"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
			"PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
			"PATH_TO_USER_TASKS_REPORT" => $arResult["PATH_TO_USER_TASKS_REPORT"],
			"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
			"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
			"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
			"PATH_TO_GROUP_TASKS" => $arParams["PATH_TO_GROUP_TASKS"],
			"PATH_TO_GROUP_TASKS_TASK" => $arParams["PATH_TO_GROUP_TASKS_TASK"],
			"PATH_TO_GROUP_TASKS_VIEW" => $arParams["PATH_TO_GROUP_TASKS_VIEW"],
			"PATH_TO_GROUP_TASKS_REPORT" => $arParams["PATH_TO_GROUP_TASKS_REPORT"],
			"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
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
			"HIDE_OWNER_IN_TITLE" => $arParams['HIDE_OWNER_IN_TITLE'],
			// "TASKS_ALWAYS_EXPANDED" => 'Y',
		],
		$component,
		["HIDE_ICONS" => "Y"]
	);
}
?>