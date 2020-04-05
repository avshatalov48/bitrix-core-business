<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

$pageId = "user_tasks_employee_plan";
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
elseif (\CModule::IncludeModule('tasks'))
{

    $APPLICATION->IncludeComponent(
        'bitrix:ui.sidepanel.wrapper',
        '',
        [
            'POPUP_COMPONENT_NAME' => 'bitrix:tasks.employee.plan',
            'POPUP_COMPONENT_TEMPLATE_NAME' => '',
            'POPUP_COMPONENT_PARAMS' => [
                "USER_ID" => $arResult["VARIABLES"]["user_id"],
                "SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
                "SET_TITLE" => $arResult["SET_TITLE"],
                "DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
                "PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
                "PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
                "PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
                "PATH_TO_GROUP_TASKS_TASK" => "",
                "PATH_TO_USER_TASKS_PROJECTS_OVERVIEW" => $arResult["PATH_TO_USER_TASKS_PROJECTS_OVERVIEW"],
                "PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
                "PATH_TO_USER_TEMPLATES_TEMPLATE" => $arResult["PATH_TO_USER_TEMPLATES_TEMPLATE"],
            ],
            'POPUP_COMPONENT_PARENT'=>$component
        ]
    );
}