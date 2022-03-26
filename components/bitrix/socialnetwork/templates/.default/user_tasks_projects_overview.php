<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;

$pageId = "user_tasks_projects_overview";
include("util_menu.php");
include("util_profile.php");

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].$this->getFolder().'/result_modifier.php');

$userId = $arResult['VARIABLES']['user_id'];

if (!CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "tasks"))
{
	echo Loc::getMessage('SU_T_TASKS_UNAVAILABLE', array(
		'#A_BEGIN#' => '<a href="'.str_replace(array("#user_id#", "#USER_ID#"), $userId, $arResult['PATH_TO_USER_FEATURES']).'">',
		'#A_END#' => '</a>'
	));
}
elseif (CModule::IncludeModule('tasks'))
{
	$userReplace = ['user_id' => $userId];

	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:tasks.projects',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => [
				'USER_ID' => $userId,
				'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
				'GRID_ID'=> 'TASKS_GRID_PROJECTS',
				'MARK_SECTION_PROJECTS_LIST' => 'Y',

				'PATH_TO_USER' => $arResult['PATH_TO_USER'],
				'PATH_TO_USER_TASKS' => $arResult['PATH_TO_USER_TASKS'],
				'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
				'PATH_TO_USER_TASKS_TEMPLATES' => $arResult['PATH_TO_USER_TASKS_TEMPLATES'],
				'PATH_TO_USER_LEAVE_GROUP' => $arResult['PATH_TO_USER_LEAVE_GROUP'],
				'PATH_TO_USER_REQUEST_GROUP' => $arResult['PATH_TO_USER_REQUEST_GROUP'],
				'PATH_TO_USER_REQUESTS' => $arResult['PATH_TO_USER_REQUESTS'],

				'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
				'PATH_TO_GROUP_CREATE' => CComponentEngine::MakePathFromTemplate(
					$arResult['PATH_TO_GROUP_CREATE'],
					$userReplace
				),
				'PATH_TO_GROUP_EDIT' => CComponentEngine::MakePathFromTemplate(
					$arResult['PATH_TO_GROUP_EDIT'],
					$userReplace
				),
				'PATH_TO_GROUP_DELETE' => CComponentEngine::MakePathFromTemplate(
					$arResult['PATH_TO_GROUP_DELETE'],
					$userReplace
				),
				'PATH_TO_GROUP_TASKS' => $arParams['PATH_TO_GROUP_TASKS'],

				'PATH_TO_TASKS' => CComponentEngine::MakePathFromTemplate(
					$arResult['PATH_TO_USER_TASKS'],
					$userReplace
				),
				'PATH_TO_TASKS_REPORT_CONSTRUCT' => CComponentEngine::MakePathFromTemplate(
					$arResult['PATH_TO_USER_TASKS_REPORT_CONSTRUCT'],
					$userReplace
				),
				'PATH_TO_TASKS_REPORT_VIEW' => CComponentEngine::MakePathFromTemplate(
					$arResult['PATH_TO_USER_TASKS_REPORT_VIEW'],
					$userReplace
				),

				'PATH_TO_REPORTS' => CComponentEngine::MakePathFromTemplate(
					$arResult['PATH_TO_USER_TASKS_REPORT'],
					$userReplace
				),
			],
			'POPUP_COMPONENT_PARENT' => $component,
		]
	);
}
