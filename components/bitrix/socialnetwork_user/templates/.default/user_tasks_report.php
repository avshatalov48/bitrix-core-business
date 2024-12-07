<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

$pageId = "user_tasks_report";
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
	if (IsModuleInstalled('report'))
	{
        $APPLICATION->IncludeComponent(
            'bitrix:ui.sidepanel.wrapper',
            '',
            [
                'POPUP_COMPONENT_NAME' => 'bitrix:tasks.report.list',
                'POPUP_COMPONENT_TEMPLATE_NAME' => '',
                'POPUP_COMPONENT_PARAMS' => [
                    "USER_ID" => $arResult["VARIABLES"]["user_id"],
                    "PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
					'PATH_TO_USER_TASKS' => $arResult["PATH_TO_USER_TASKS"],
                    "PATH_TO_TASKS" => CComponentEngine::MakePathFromTemplate(
                        $arResult["PATH_TO_USER_TASKS"],
                        array('user_id' => $arResult["VARIABLES"]["user_id"])
                    ),
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
                    "PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"]
                ]
            ]
        );

//		$APPLICATION->IncludeComponent(
//			"bitrix:tasks.report.list",
//			"",
//			array(
//				"USER_ID" => $arResult["VARIABLES"]["user_id"],
//				"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
//				"PATH_TO_TASKS" => CComponentEngine::MakePathFromTemplate(
//					$arResult["PATH_TO_USER_TASKS"],
//					array('user_id' => $arResult["VARIABLES"]["user_id"])
//				),
//				"PATH_TO_TASKS_REPORT" => CComponentEngine::MakePathFromTemplate(
//					$arResult["PATH_TO_USER_TASKS_REPORT"],
//					array('user_id' => $arResult["VARIABLES"]["user_id"])
//				),
//				"PATH_TO_TASKS_REPORT_CONSTRUCT" => CComponentEngine::MakePathFromTemplate(
//					$arResult["PATH_TO_USER_TASKS_REPORT_CONSTRUCT"],
//					array('user_id' => $arResult["VARIABLES"]["user_id"])
//				),
//				"PATH_TO_TASKS_REPORT_VIEW" => CComponentEngine::MakePathFromTemplate(
//					$arResult["PATH_TO_USER_TASKS_REPORT_VIEW"],
//					array('user_id' => $arResult["VARIABLES"]["user_id"])
//				),
//				'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
//				"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"]
//			),
//			false
//		);
	}
	else
	{

        $APPLICATION->IncludeComponent(
            'bitrix:ui.sidepanel.wrapper',
            '',
            [
                'POPUP_COMPONENT_NAME' => 'bitrix:tasks.report',
                'POPUP_COMPONENT_TEMPLATE_NAME' => '',
                'POPUP_COMPONENT_PARAMS' => [
                    "USER_ID" => $arResult["VARIABLES"]["user_id"],
                    "ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
                    "PAGE_VAR" => $arResult["ALIASES"]["page"] ?? null,
                    "USER_VAR" => $arResult["ALIASES"]["user_id"] ?? null,
                    "VIEW_VAR" => $arResult["ALIASES"]["view_id"] ?? null,
                    "TASK_VAR" => $arResult["ALIASES"]["task_id"] ?? null,
                    "ACTION_VAR" => $arResult["ALIASES"]["action"] ?? null,
                    "PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
                    "PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
                    "PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
                    "PATH_TO_USER_TASKS_REPORT" => $arResult["PATH_TO_USER_TASKS_REPORT"],
                    "PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
                    'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
                    "PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
                    "PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"] ?? null,
                    "PATH_TO_GROUP_TASKS_TASK" => $arResult["PATH_TO_GROUP_TASKS_TASK"] ?? null,
                    "PATH_TO_GROUP_TASKS_VIEW" => $arResult["PATH_TO_GROUP_TASKS_VIEW"] ?? null,
                    "PATH_TO_GROUP_TASKS_REPORT" => $arResult["PATH_TO_GROUP_TASKS_REPORT"] ?? null,
                    "PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
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
                ]
            ]
        );

//		$APPLICATION->IncludeComponent(
//			"bitrix:tasks.report",
//			".default",
//			Array(
//				"USER_ID" => $arResult["VARIABLES"]["user_id"],
//				"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
//				"PAGE_VAR" => $arResult["ALIASES"]["page"],
//				"USER_VAR" => $arResult["ALIASES"]["user_id"],
//				"VIEW_VAR" => $arResult["ALIASES"]["view_id"],
//				"TASK_VAR" => $arResult["ALIASES"]["task_id"],
//				"ACTION_VAR" => $arResult["ALIASES"]["action"],
//				"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
//				"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
//				"PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
//				"PATH_TO_USER_TASKS_REPORT" => $arResult["PATH_TO_USER_TASKS_REPORT"],
//				"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
//				'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
//				"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
//				"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
//				"PATH_TO_GROUP_TASKS_TASK" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
//				"PATH_TO_GROUP_TASKS_VIEW" => $arResult["PATH_TO_GROUP_TASKS_VIEW"],
//				"PATH_TO_GROUP_TASKS_REPORT" => $arResult["PATH_TO_GROUP_TASKS_REPORT"],
//				"PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
//				"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
//				"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
//				"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
//				"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
//				"SET_TITLE" => $arResult["SET_TITLE"],
//				"FORUM_ID" => $arParams["TASK_FORUM_ID"],
//				"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
//				"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
//				"USE_THUMBNAIL_LIST" => "N",
//				"INLINE" => "Y",
//			),
//			$component,
//			array("HIDE_ICONS" => "Y")
//		);
	}

}
