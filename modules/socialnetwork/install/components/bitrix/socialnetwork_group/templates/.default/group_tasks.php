<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "group_tasks";
include("util_group_menu.php");
include("util_group_profile.php");

if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "tasks"))
{
	if(class_exists('Bitrix\Tasks\Ui\Filter\Task'))
	{
		\Bitrix\Tasks\Ui\Filter\Task::setGroupId($arResult[ "VARIABLES" ][ "group_id" ]);
		$state = \Bitrix\Tasks\Ui\Filter\Task::listStateInit()->getState();

		switch ($state[ 'VIEW_SELECTED' ][ 'CODENAME' ])
		{
			case 'VIEW_MODE_GANTT':
				$componentName = 'bitrix:tasks.task.gantt';
				break;
			case 'VIEW_MODE_PLAN':
			case 'VIEW_MODE_KANBAN':
				$componentName = 'bitrix:tasks.kanban';
				break;
//			case 'VIEW_MODE_TIMELINE':
//				$componentName = 'bitrix:tasks.timeline';
//				break;
			default:
				$componentName = 'bitrix:tasks.task.list';
				break;
		}
	}
	else
	{
		$componentName = 'bitrix:tasks.list';
	}

	$APPLICATION->IncludeComponent(
		$componentName,
		".default",
		Array(
			"INCLUDE_INTERFACE_HEADER" => "Y",
			"PERSONAL" => $state["VIEW_SELECTED"]["CODENAME"] == "VIEW_MODE_PLAN" ? "Y" : "N",
			"KANBAN_SHOW_VIEW_MODE"=>'Y',
			"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
			"ITEMS_COUNT" => "50",
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
			"VIEW_VAR" => $arResult["ALIASES"]["view_id"],
			"TASK_VAR" => $arResult["ALIASES"]["task_id"],
			"ACTION_VAR" => $arResult["ALIASES"]["action"],
			"PATH_TO_USER_TASKS_TEMPLATES" => $arParams["PATH_TO_USER_TASKS_TEMPLATES"],
			"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
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
			"SHOW_YEAR" => $arParams["SHOW_YEAR"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"USE_THUMBNAIL_LIST" => "N",
			"INLINE" => "Y",
			"HIDE_OWNER_IN_TITLE" => $arParams['HIDE_OWNER_IN_TITLE'],
			"TASKS_ALWAYS_EXPANDED" => 'Y'
		),
		$component,
		array("HIDE_ICONS" => "Y")
	);
}
?>