<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

$pageId = "user_tasks_recyclebin";
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
        "bitrix:ui.sidepanel.wrapper",
        "",
        array(
            'POPUP_COMPONENT_NAME' => 'bitrix:tasks.recyclebin.list',
            "POPUP_COMPONENT_TEMPLATE_NAME" => "",
            "POPUP_COMPONENT_PARAMS" => array (
                "USER_ID"                              => $arResult["VARIABLES"]["user_id"],
                "ITEMS_COUNT"                          => "50",
                "PAGE_VAR"                             => $arResult["ALIASES"]["page"] ?? null,
                "USER_VAR"                             => $arResult["ALIASES"]["user_id"] ?? null,
                "VIEW_VAR"                             => $arResult["ALIASES"]["view_id"] ?? null,
                "TASK_VAR"                             => $arResult["ALIASES"]["task_id"] ?? null,
                "ACTION_VAR"                           => $arResult["ALIASES"]["action"] ?? null,
                "PATH_TO_USER_PROFILE"                 => $arResult["PATH_TO_USER"] ?? null,
                "PATH_TO_MESSAGES_CHAT"                => $arResult["PATH_TO_MESSAGES_CHAT"] ?? null,
                "PATH_TO_CONPANY_DEPARTMENT"           => $arParams["PATH_TO_CONPANY_DEPARTMENT"] ?? null,
                "PATH_TO_VIDEO_CALL"                   => $arResult["PATH_TO_VIDEO_CALL"] ?? null,
                "PATH_TO_TRASH"                        => $arResult["PATH_TO_USER_TASKS_TRASH"] ?? null,
                "PATH_TO_TRASH_ACTION"                 => $arResult["PATH_TO_USER_TASKS_TRASH_ACTION"] ?? null,
                "PATH_TO_USER_TASKS"                   => $arResult["PATH_TO_USER_TASKS"] ?? null,
                "PATH_TO_USER_TASKS_BOARD"             => $arResult["PATH_TO_USER_TASKS_BOARD"] ?? null,
                "PATH_TO_USER_TASKS_TASK"              => $arResult["PATH_TO_USER_TASKS_TASK"] ?? null,
                "PATH_TO_USER_TASKS_VIEW"              => $arResult["PATH_TO_USER_TASKS_VIEW"] ?? null,
                "PATH_TO_USER_TASKS_REPORT"            => $arResult["PATH_TO_USER_TASKS_REPORT"] ?? null,
                "PATH_TO_USER_TASKS_TEMPLATES"         => $arResult["PATH_TO_USER_TASKS_TEMPLATES"] ?? null,
                "PATH_TO_GROUP"                        => $arParams["PATH_TO_GROUP"] ?? null,
                "PATH_TO_GROUP_TASKS"                  => $arParams["PATH_TO_GROUP_TASKS"] ?? null,
                "PATH_TO_GROUP_TASKS_BOARD"            => $arParams["PATH_TO_GROUP_TASKS_BOARD"] ?? null,
                "PATH_TO_GROUP_TASKS_TASK"             => $arParams["PATH_TO_GROUP_TASKS_TASK"] ?? null,
                "PATH_TO_GROUP_TASKS_VIEW"             => $arParams["PATH_TO_GROUP_TASKS_VIEW"] ?? null,
                "PATH_TO_GROUP_TASKS_REPORT"           => $arParams["PATH_TO_GROUP_TASKS_REPORT"] ?? null,
                'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'] ?? null,
                "SET_NAV_CHAIN"                        => $arResult["SET_NAV_CHAIN"] ?? null,
                "SET_TITLE"                            => $arResult["SET_TITLE"] ?? null,
                "FORUM_ID"                             => $arParams["TASK_FORUM_ID"] ?? null,
                "NAME_TEMPLATE"                        => $arParams["NAME_TEMPLATE"] ?? null,
                "SHOW_LOGIN"                           => $arParams["SHOW_LOGIN"] ?? null,
                "DATE_TIME_FORMAT"                     => $arResult["DATE_TIME_FORMAT"] ?? null,
                "SHOW_YEAR"                            => $arParams["SHOW_YEAR"] ?? null,
                "CACHE_TYPE"                           => $arParams["CACHE_TYPE"] ?? null,
                "CACHE_TIME"                           => $arParams["CACHE_TIME"] ?? null,
                "USE_THUMBNAIL_LIST"                   => "N",
                "INLINE"                               => "Y",
                "USE_PAGINATION"                       => 'Y',
                'HIDE_OWNER_IN_TITLE'                  => $arParams['HIDE_OWNER_IN_TITLE'] ?? null,
                "PREORDER"                             => array('STATUS_COMPLETE' => 'asc')
            ),
            "POPUP_COMPONENT_PARENT" => $component
        )
    );
}