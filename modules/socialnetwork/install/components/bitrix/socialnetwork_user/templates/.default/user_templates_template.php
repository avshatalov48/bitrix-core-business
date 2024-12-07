<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
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

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$pageId = 'user_tasks';
$userId = (int)$arResult['VARIABLES']['user_id'];
$templateId = (int)$arResult['VARIABLES']['template_id'];
$action = $arResult['VARIABLES']['action'];

if (Context::getCurrent()->getRequest()->get('IFRAME'))
{
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
	elseif (Loader::includeModule('tasks'))
	{
		$context = Context::getCurrent()->getRequest()->get('context');

		if ($context === 'flow' && $templateId <= 0)
		{
			$popupComponentTemplate = 'flow';
		}
		elseif ($action === 'edit')
		{
			$popupComponentTemplate = '';
		}
		else
		{
			$popupComponentTemplate = 'view';
		}

		$popupComponentParams = [
			"USER_ID" => $userId,
			"ID" => $templateId,
			"PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
			"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_COMPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
			"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
			"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
			"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
			"PATH_TO_USER_TEMPLATES_TEMPLATE" => $arResult["PATH_TO_USER_TEMPLATES_TEMPLATE"],
			'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arResult['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],
			"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
			"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
			"SET_TITLE" => $arResult["SET_TITLE"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
		];

		$APPLICATION->IncludeComponent(
			'bitrix:ui.sidepanel.wrapper',
			'',
			[
				'PAGE_MODE' => false,
				'POPUP_COMPONENT_NAME' => 'bitrix:tasks.task.template',
				'POPUP_COMPONENT_TEMPLATE_NAME' => $popupComponentTemplate,
				'POPUP_COMPONENT_PARAMS' => $popupComponentParams,
			],
			$component,

		);
	}
}
else
{
	$userId = CurrentUser::get()->getId();
	$backgroundForTemplate = true;
	require_once('user_templates_template_background.php');
}