<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$pageId = "user_tasks";
include("util_menu.php");
include("util_profile.php");

if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "tasks"))
{
	if(array_key_exists('legacy', $_REQUEST))
	{
		$APPLICATION->IncludeComponent(
			"bitrix:tasks.template.edit",
			($arResult["VARIABLES"]["action"] == "edit" ? '.default' : 'detail'),
			Array(
				"USER_ID" => $arResult["VARIABLES"]["user_id"],
				"PAGE_VAR" => $arResult["ALIASES"]["page"],
				"USER_VAR" => $arResult["ALIASES"]["user_id"],
				"VIEW_VAR" => $arResult["ALIASES"]["view_id"],
				"TASK_VAR" => $arResult["ALIASES"]["task_id"],
				"ACTION_VAR" => $arResult["ALIASES"]["action"],
				"ACTION" => $arResult["VARIABLES"]["action"],
				"TEMPLATE_ID" => $arResult["VARIABLES"]["template_id"],
				"PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
				"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
				"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
				"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
				"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
				"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
				"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
				"PATH_TO_USER_TEMPLATES_TEMPLATE" => $arResult["PATH_TO_USER_TEMPLATES_TEMPLATE"],
				'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
				"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
				"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
				"SET_TITLE" => $arResult["SET_TITLE"],
				"FORUM_ID" => $arParams["TASK_FORUM_ID"],
				"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
				"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
				"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
				"SHOW_YEAR" => $arParams["SHOW_YEAR"],
				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"CACHE_TIME" => $arParams["CACHE_TIME"],
				"THUMBNAIL_LIST_SIZE" => 30,
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
	}
	else
	{
		$APPLICATION->IncludeComponent(
			"bitrix:tasks.task.template",
			($arResult["VARIABLES"]["action"] == "edit" ? '.default' : 'view'),
			Array(
				"USER_ID" => $arResult["VARIABLES"]["user_id"],
				"ID" => $arResult["VARIABLES"]["template_id"],
				"PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
				"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
				"PATH_TO_COMPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
				"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
				"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
				"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
				"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
				"PATH_TO_USER_TEMPLATES_TEMPLATE" => $arResult["PATH_TO_USER_TEMPLATES_TEMPLATE"],
				'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
				"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
				"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
				"SET_TITLE" => $arResult["SET_TITLE"],
				"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
				"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"CACHE_TIME" => $arParams["CACHE_TIME"],
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
	}
}
?>