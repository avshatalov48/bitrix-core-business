<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

$pageId = "user_tasks_report_view";
include("util_menu.php");
include("util_profile.php");

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].$this->getFolder().'/result_modifier.php');

if (!CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "tasks"))
{
	echo Loc::getMessage('SU_T_TASKS_UNAVAILABLE', array(
		'#A_BEGIN#' => '<a href="'.str_replace(array("#user_id#", "#USER_ID#"), $arResult['VARIABLES']['user_id'], $arResult['PATH_TO_USER_FEATURES']).'">',
		'#A_END#' => '</a>'
	));
}
else
{
	$APPLICATION->IncludeComponent(
				"bitrix:tasks.report.view",
				"",
				Array(
					"USER_ID" => $arResult["VARIABLES"]["user_id"],
					"REPORT_ID" => $arResult["VARIABLES"]["report_id"],
					"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
					"USER_NAME_FORMAT" => $arParams["NAME_TEMPLATE"],
					"ROWS_PER_PAGE" => $arParams["ITEM_DETAIL_COUNT"],
					"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
					"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
					"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
					"PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
					"PATH_TO_TASKS_REPORT" => CComponentEngine::MakePathFromTemplate(
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
					'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
					"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
					),
				false
				);

}
?>