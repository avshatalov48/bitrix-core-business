<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$pageId = "user_tasks";
include("util_menu.php");
include("util_profile.php");

if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "tasks"))
{
	$APPLICATION->IncludeComponent(
		"bitrix:tasks.iframe.popup",
		"wrap",
		array(
			"ACTION" => $arResult["VARIABLES"]["action"] === "edit" ? "edit" : "view",
			"FORM_PARAMETERS" => array(
				"ID" => $arResult["VARIABLES"]["task_id"],
				"GROUP_ID" => "",
				"USER_ID" => $arResult["VARIABLES"]["user_id"],
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
			)
		),
		$component,
		array("HIDE_ICONS" => "Y")
	);
}
