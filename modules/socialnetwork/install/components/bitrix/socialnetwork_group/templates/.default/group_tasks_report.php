<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "group_tasks_report";
include("util_group_menu.php");
include("util_group_profile.php");
?>
<?
if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "tasks"))
{
	if (IsModuleInstalled('report'))
	{
		$APPLICATION->IncludeComponent(
			"bitrix:tasks.report.list",
			"",
			array(
				"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
				"PATH_TO_TASKS" => CComponentEngine::MakePathFromTemplate(
					$arResult["PATH_TO_GROUP_TASKS"],
					array('group_id' => $arResult["VARIABLES"]["group_id"])
				),
				"PATH_TO_TASKS_REPORT" => CComponentEngine::MakePathFromTemplate(
					$arResult["PATH_TO_GROUP_TASKS_REPORT"],
					array('group_id' => $arResult["VARIABLES"]["group_id"])
				),
				"PATH_TO_TASKS_REPORT_CONSTRUCT" => CComponentEngine::MakePathFromTemplate(
					$arResult["PATH_TO_GROUP_TASKS_REPORT_CONSTRUCT"],
					array('group_id' => $arResult["VARIABLES"]["group_id"])
				),
				"PATH_TO_TASKS_REPORT_VIEW" => CComponentEngine::MakePathFromTemplate(
					$arResult["PATH_TO_GROUP_TASKS_REPORT_VIEW"],
					array('group_id' => $arResult["VARIABLES"]["group_id"])
				),
				"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"]
			),
			false
		);
	}
	else
	{
		$APPLICATION->IncludeComponent(
			"bitrix:tasks.report",
			".default",
			Array(
				"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
				"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
				"PAGE_VAR" => $arResult["ALIASES"]["page"],
				"USER_VAR" => $arResult["ALIASES"]["user_id"],
				"VIEW_VAR" => $arResult["ALIASES"]["view_id"],
				"TASK_VAR" => $arResult["ALIASES"]["task_id"],
				"ACTION_VAR" => $arResult["ALIASES"]["action"],
				"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
				"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
				"PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
				"PATH_TO_USER_TASKS_REPORT" => $arResult["PATH_TO_USER_TASKS_REPORT"],
				"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
				"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
				"PATH_TO_GROUP_TASKS_TASK" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
				"PATH_TO_GROUP_TASKS_VIEW" => $arResult["PATH_TO_GROUP_TASKS_VIEW"],
				"PATH_TO_GROUP_TASKS_REPORT" => $arResult["PATH_TO_GROUP_TASKS_REPORT"],
				"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
				"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
				"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
				"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
				"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
				"SET_TITLE" => $arResult["SET_TITLE"],
				"FORUM_ID" => $arParams["TASK_FORUM_ID"],
				"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
				"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
				"USE_THUMBNAIL_LIST" => "N",
				"INLINE" => "Y",
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
	}
}
?>