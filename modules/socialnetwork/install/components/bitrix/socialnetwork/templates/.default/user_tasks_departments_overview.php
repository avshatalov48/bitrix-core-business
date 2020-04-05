<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$pageId = "user_tasks_departments_overview";
include("util_menu.php");
include("util_profile.php");

if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "tasks"))
{
	$APPLICATION->IncludeComponent(
		"bitrix:tasks.departments_overview",
		"",
		array(
			"USER_ID" => $arResult["VARIABLES"]["user_id"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
			"PATH_TO_USER" => $arResult["PATH_TO_USER"],
			'PATH_TO_USER_TASKS' => $arResult['PATH_TO_USER_TASKS'],
			"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
			"PATH_TO_TASKS" => CComponentEngine::MakePathFromTemplate(
				$arResult["PATH_TO_USER_TASKS"],
				array('user_id' => $arResult["VARIABLES"]["user_id"])
			),
			"PATH_TO_REPORTS" => CComponentEngine::MakePathFromTemplate(
				$arResult["PATH_TO_USER_TASKS_REPORT"],
				array('user_id' => $arResult["VARIABLES"]["user_id"])
			),
			"PATH_TO_TASKS_REPORT_CONSTRUCT" => CComponentEngine::MakePathFromTemplate(
				$arResult["PATH_TO_USER_TASKS_REPORT_CONSTRUCT"],
				array('user_id' => $arResult["VARIABLES"]["user_id"])
			),
			"PATH_TO_TASKS_REPORT_VIEW" => CComponentEngine::MakePathFromTemplate(
				$arResult["PATH_TO_USER_TASKS_REPORT_VIEW"],
				array('user_id' => $arResult["VARIABLES"]["user_id"])
			),
			"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"]
		),
		false
	);
}
