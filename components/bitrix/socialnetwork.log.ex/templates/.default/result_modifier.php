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

use Bitrix\Main\Config\Option;

global $USER;

if (IsModuleInstalled('tasks'))
{
	$userPage = Option::get('socialnetwork', 'user_page', SITE_DIR.'company/personal/');
	$workgroupPage = Option::get('socialnetwork', 'workgroups_page', SITE_DIR.'workgroups/');

	$arParams['PATH_TO_USER_TASKS'] = (!empty($arParams['PATH_TO_USER_TASKS']) ? $arParams['PATH_TO_USER_TASKS'] : $userPage.'user/#user_id#/tasks/');
	$arParams['PATH_TO_USER_TASKS_TASK'] = (!empty($arParams['PATH_TO_USER_TASKS_TASK']) ? $arParams['PATH_TO_USER_TASKS_TASK'] : $userPage.'user/#user_id#/tasks/task/#action#/#task_id#/');
	$arParams['PATH_TO_GROUP_TASKS'] = (!empty($arParams['PATH_TO_GROUP_TASKS']) ? $arParams['PATH_TO_GROUP_TASKS'] : $workgroupPage.'group/#group_id#/tasks/');
	$arParams['PATH_TO_GROUP_TASKS_TASK'] = (!empty($arParams['PATH_TO_GROUP_TASKS_TASK']) ? $arParams['PATH_TO_GROUP_TASKS_TASK'] : $workgroupPage.'group/#group_id#/tasks/task/#action#/#task_id#/');
	$arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'] = (!empty($arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW']) ? $arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'] : $userPage.'user/#user_id#/tasks/projects/');
	$arParams['PATH_TO_USER_TASKS_SCRUM_OVERVIEW'] = (!empty($arParams['PATH_TO_USER_TASKS_SCRUM_OVERVIEW']) ? $arParams['PATH_TO_USER_TASKS_SCRUM_OVERVIEW'] : $userPage.'user/#user_id#/tasks/scrum/');
	$arParams['PATH_TO_USER_TASKS_TEMPLATES'] = (!empty($arParams['PATH_TO_USER_TASKS_TEMPLATES']) ? $arParams['PATH_TO_USER_TASKS_TEMPLATES'] : $userPage.'user/#user_id#/tasks/templates/');
	$arParams['PATH_TO_USER_TEMPLATES_TEMPLATE'] = (!empty($arParams['PATH_TO_USER_TEMPLATES_TEMPLATE']) ? $arParams['PATH_TO_USER_TEMPLATES_TEMPLATE'] : $userPage.'user/#user_id#/tasks/templates/template/#action#/#template_id#/');
}

$formTargetId = false;
$informerTargetId = false;
if (defined("BITRIX24_INDEX_PAGE"))
{
	$formTargetId = "topblock";
	$informerTargetId = "inside_pagetitle";
}
else
{
	if (isset($arParams["FORM_TARGET_ID"]))
	{
		$formTargetId = $arParams["FORM_TARGET_ID"];
	}

	if (isset($arParams["INFORMER_TARGET_ID"]))
	{
		$informerTargetId = $arParams["INFORMER_TARGET_ID"];
	}
}

$arResult['TOP_RATING_DATA'] = (
	\Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
	&& !empty($arResult["arLogTmpID"])
		? \Bitrix\Socialnetwork\ComponentHelper::getLivefeedRatingData(array(
			'logId' => array_unique(array_merge($arResult["arLogTmpID"], $arResult["pinnedIdList"])),
		))
		: array()
);

$arResult["FORM_TARGET_ID"] = $formTargetId;
$arResult["INFORMER_TARGET_ID"] = $informerTargetId;

$arResult["EMPTY_AJAX_FEED"] = (
	$arResult["AJAX_CALL"]
	&& (
		!$arResult["Events"]
		|| !is_array($arResult["Events"])
		|| empty($arResult["Events"])
	)
);

$arResult['PAGE_MODE'] = 'first';
if ($arResult["bReload"])
{
	$arResult['PAGE_MODE'] = 'refresh';
}
elseif ($arResult["AJAX_CALL"])
{
	$arResult['PAGE_MODE'] = 'next';
}

$arResult["SHOW_NOTIFICATION_NOTASKS"] = false;
if (
	$arParams["IS_CRM"] !== "Y"
	&& $USER->isAuthorized()
	&& !\Bitrix\Socialnetwork\ComponentHelper::checkLivefeedTasksAllowed()
	&& \Bitrix\Main\ModuleManager::isModuleInstalled('tasks')
)
{
	$arResult["SHOW_NOTIFICATION_NOTASKS"] = (\CUserOptions::getOption('socialnetwork', '~log_notasks_notification_read', 'N') !== 'Y');
}

$arParams['IMAGE_MAX_WIDTH'] = 600;
$arParams['IMAGE_MAX_HEIGHT'] = 600;