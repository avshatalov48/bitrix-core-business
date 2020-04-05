<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "group_tasks";
include("util_group_menu.php");
include("util_group_profile.php");

if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "tasks"))
{
	$APPLICATION->IncludeComponent(
		"bitrix:tasks.iframe.popup",
		"wrap",
		array(
			"ACTION" => $arResult["VARIABLES"]["action"] === "edit" ? "edit" : "view",
			"FORM_PARAMETERS" => array(

				"ID" => $arResult["VARIABLES"]["task_id"],
				"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
				//"USER_ID" => $arResult["VARIABLES"]["user_id"],

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
			),
			'HIDE_MENU_PANEL'=>'Y'
		),
		$component,
		array("HIDE_ICONS" => "Y")
	);
}