<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var bool $backgroundForTemplate */
/** @var int $templateId */
/** @var string $action */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CBitrixComponent $component */

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

$pageId = 'user_tasks';
$userId = $arResult['VARIABLES']['user_id'];

include("util_menu.php");
include("util_profile.php");

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'] . $this->getFolder() . '/result_modifier.php');

if (
	!CSocNetFeatures::IsActiveFeature(
		SONET_ENTITY_USER,
		$userId,
		'tasks'
	)
)
{
	$pathToUserFeatures = str_replace(["#user_id#", "#USER_ID#"], $userId, $arResult['PATH_TO_USER_FEATURES']);
	$pathToUserFeaturesHref = '<a href="' . $pathToUserFeatures . '">';

	echo Loc::getMessage('SU_T_TASKS_UNAVAILABLE', [
		'#A_BEGIN#' => $pathToUserFeaturesHref,
		'#A_END#' => '</a>',
	]);
}
elseif (\CModule::IncludeModule('tasks'))
{
	$getParams = [];
	if ($backgroundForTemplate)
	{
		$getParams = '?' . http_build_query(Context::getCurrent()->getRequest()->getQueryList()->toArray());
	}
	$componentParams = [
		"USER_ID" => $userId,
		"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"VIEW_VAR" => $arResult["ALIASES"]["view_id"],
		"TASK_VAR" => $arResult["ALIASES"]["task_id"],
		"TEMPLATE_VAR" => $arResult["ALIASES"]["template_id"],
		"ACTION_VAR" => $arResult["ALIASES"]["action"],
		"PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
		"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
		"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
		"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
		"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
		"PATH_TO_USER_TASKS_VIEW" => $arResult["PATH_TO_USER_TASKS_VIEW"],
		"PATH_TO_USER_TASKS_REPORT" => $arResult["PATH_TO_USER_TASKS_REPORT"],
		"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
		"PATH_TO_USER_TEMPLATES_TEMPLATE" => $arResult["PATH_TO_USER_TEMPLATES_TEMPLATE"],
		'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
		"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
		"PATH_TO_GROUP_TASKS" => $arParams["PATH_TO_GROUP_TASKS"],
		"PATH_TO_GROUP_TASKS_TASK" => $arParams["PATH_TO_GROUP_TASKS_TASK"],
		"PATH_TO_GROUP_TASKS_VIEW" => $arParams["PATH_TO_GROUP_TASKS_VIEW"],
		"PATH_TO_GROUP_TASKS_REPORT" => $arParams["PATH_TO_GROUP_TASKS_REPORT"],
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
		'BACKGROUND_FOR_TEMPLATE' => $backgroundForTemplate,
		'TEMPLATE_ID' => $templateId,
		'TEMPLATE_ACTION' => $action,
		'GET_PARAMS' => $getParams,
	];
	$APPLICATION->IncludeComponent(
		"bitrix:ui.sidepanel.wrapper",
		"",
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:tasks.templates.list',
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" => $componentParams,
			"POPUP_COMPONENT_PARENT" => $component,
		]
	);
}