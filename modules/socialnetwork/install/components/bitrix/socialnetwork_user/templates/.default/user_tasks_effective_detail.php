<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "user_tasks_effective_detail";
include("util_menu.php");
include("util_profile.php");

if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "tasks"))
{

	$APPLICATION->IncludeComponent(
		'bitrix:tasks.report.effective.detail',
		".default",
		Array(
			"USER_ID" => $arResult["VARIABLES"]["user_id"],
			"ITEMS_COUNT" => "50",
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"USER_VAR" => $arResult["ALIASES"]["user_id"],
			"VIEW_VAR" => $arResult["ALIASES"]["view_id"],
			"TASK_VAR" => $arResult["ALIASES"]["task_id"],
			"ACTION_VAR" => $arResult["ALIASES"]["action"],
			"PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
			"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
			"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
			"PATH_TO_USER_TASKS_BOARD" => $arResult["PATH_TO_USER_TASKS_BOARD"],
			"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
			"PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
			"PATH_TO_USER_TASKS_REPORT" => $arResult["PATH_TO_USER_TASKS_REPORT"],
			"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
			"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
			"PATH_TO_GROUP_TASKS" => $arParams["PATH_TO_GROUP_TASKS"],
			"PATH_TO_GROUP_TASKS_BOARD" => $arParams["PATH_TO_GROUP_TASKS_BOARD"],
			"PATH_TO_GROUP_TASKS_TASK" => $arParams["PATH_TO_GROUP_TASKS_TASK"],
			"PATH_TO_GROUP_TASKS_VIEW" => $arParams["PATH_TO_GROUP_TASKS_VIEW"],
			"PATH_TO_GROUP_TASKS_REPORT" => $arParams["PATH_TO_GROUP_TASKS_REPORT"],
			'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
			"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
			"SET_TITLE" => $arResult["SET_TITLE"],
			"FORUM_ID" => $arParams["TASK_FORUM_ID"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
			"SHOW_YEAR" => $arParams["SHOW_YEAR"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"USE_THUMBNAIL_LIST" => "N",
			"INLINE" => "Y",
			"USE_PAGINATION"=>'Y',
			'HIDE_OWNER_IN_TITLE' => $arParams['HIDE_OWNER_IN_TITLE'],
			"PREORDER" => array('STATUS_COMPLETE' => 'asc')
		),
		$component,
		array("HIDE_ICONS" => "Y")
	);
}
?>