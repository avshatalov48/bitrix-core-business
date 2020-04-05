<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "user_tasks_employee_plan";
include("util_menu.php");
include("util_profile.php");
?>
<?
if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "tasks"))
{
	$APPLICATION->IncludeComponent('bitrix:tasks.employee.plan', '', array(
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
		"PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
		"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
		"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
		"PATH_TO_GROUP_TASKS_TASK" => "",
		"PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
		"PATH_TO_USER_TASKS_PROJECTS_OVERVIEW" => $arResult["PATH_TO_USER_TASKS_PROJECTS_OVERVIEW"],
		"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
		"PATH_TO_USER_TEMPLATES_TEMPLATE" => $arResult["PATH_TO_USER_TEMPLATES_TEMPLATE"],
	), $component);
}