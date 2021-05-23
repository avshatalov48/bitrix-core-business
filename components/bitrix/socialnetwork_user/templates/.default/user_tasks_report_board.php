<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$pageId = "user_tasks_reportboard";
include("util_menu.php");
include("util_profile.php");
if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "tasks"))
{
    $APPLICATION->IncludeComponent(
        'bitrix:ui.sidepanel.wrapper',
        '',
        [
            'POPUP_COMPONENT_NAME' => 'bitrix:tasks.reportboard',
            'POPUP_COMPONENT_TEMPLATE_NAME' => '',
            'POPUP_COMPONENT_PARAMS' => [
                "USER_ID" => $arResult["VARIABLES"]["user_id"],
                "ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
                "PAGE_VAR" => $arResult["ALIASES"]["page"],
                "USER_VAR" => $arResult["ALIASES"]["user_id"],
                "VIEW_VAR" => $arResult["ALIASES"]["view_id"],
                "TASK_VAR" => $arResult["ALIASES"]["task_id"],
                "ACTION_VAR" => $arResult["ALIASES"]["action"],
                "PATH_TO_REPORT" => $arResult["PATH_TO_USER_TASKS"],
                "PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
                "PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
                "PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
                "PATH_TO_USER_TASKS_REPORT" => $arResult["PATH_TO_USER_TASKS_REPORT"],
                "PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
                'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
                "PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
                "PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
                "PATH_TO_GROUP_TASKS_TASK" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
                "PATH_TO_GROUP_TASKS_VIEW" => $arResult["PATH_TO_GROUP_TASKS_VIEW"],
                "PATH_TO_GROUP_TASKS_REPORT" => $arResult["PATH_TO_GROUP_TASKS_REPORT"],
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

//	$APPLICATION->IncludeComponent(
//		"bitrix:tasks.reportboard",
//		".default",
//		Array(
//			"USER_ID" => $arResult["VARIABLES"]["user_id"],
//			"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
//			"PAGE_VAR" => $arResult["ALIASES"]["page"],
//			"USER_VAR" => $arResult["ALIASES"]["user_id"],
//			"VIEW_VAR" => $arResult["ALIASES"]["view_id"],
//			"TASK_VAR" => $arResult["ALIASES"]["task_id"],
//			"ACTION_VAR" => $arResult["ALIASES"]["action"],
//			"PATH_TO_REPORT" => $arResult["PATH_TO_USER_TASKS"],
//			"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
//			"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
//			"PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
//			"PATH_TO_USER_TASKS_REPORT" => $arResult["PATH_TO_USER_TASKS_REPORT"],
//			"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
//			'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
//			"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
//			"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
//			"PATH_TO_GROUP_TASKS_TASK" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
//			"PATH_TO_GROUP_TASKS_VIEW" => $arResult["PATH_TO_GROUP_TASKS_VIEW"],
//			"PATH_TO_GROUP_TASKS_REPORT" => $arResult["PATH_TO_GROUP_TASKS_REPORT"],
//			"PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
//			"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
//			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
//			"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
//			"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
//			"SET_TITLE" => $arResult["SET_TITLE"],
//			"FORUM_ID" => $arParams["TASK_FORUM_ID"],
//			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
//			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
//			"USE_THUMBNAIL_LIST" => "N",
//			"INLINE" => "Y",
//		),
//		$component,
//		array("HIDE_ICONS" => "Y")
//	);
}