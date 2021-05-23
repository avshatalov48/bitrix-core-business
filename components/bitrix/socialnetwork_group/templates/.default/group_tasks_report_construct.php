<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
?>
<?
$pageId = "group_tasks_report_construct";
include("util_group_menu.php");
include("util_group_profile.php");

if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "tasks"))
{
	$APPLICATION->IncludeComponent(
		"bitrix:tasks.report.construct",
		"",
		array(
			"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
			"REPORT_ID" => $arResult["VARIABLES"]["report_id"],
			"ACTION" => $arResult["VARIABLES"]["action"],
			'PATH_TO_GROUP_TASKS' => CComponentEngine::MakePathFromTemplate(
				$arResult["PATH_TO_GROUP_TASKS"],
				array('group_id' => $arResult["VARIABLES"]["group_id"])
			),
			"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
			"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
			"PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
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
			"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
		),
		false
	);

}
?>