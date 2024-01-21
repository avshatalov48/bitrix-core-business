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

$groupId = (int) ($arResult['VARIABLES']['group_id'] ?? 0);
$userId = (int) ($arResult['VARIABLES']['user_id'] ?? 0);
if (!$userId)
{
	$userId = (int) $USER->getID();
}

$componentParams = [
	'GROUP_ID' => $groupId,
	'USER_ID' => $userId,

	'PATH_TO_USER' => $arResult['PATH_TO_USER'],
	'PATH_TO_GROUP' => $arResult['PATH_TO_GROUP'],

	'PATH_TO_USER_DISCUSSIONS' => $arResult['PATH_TO_USER_DISCUSSIONS'],
	'PATH_TO_USER_TASKS' => $arResult['PATH_TO_USER_TASKS'],
	'PATH_TO_USER_CALENDAR' => $arResult['PATH_TO_USER_CALENDAR'],
	'PATH_TO_USER_FILES' => $arResult['PATH_TO_USER_FILES'],

	'PATH_TO_GROUP_DISCUSSIONS' => $arResult['PATH_TO_GROUP_DISCUSSIONS'],
	'PATH_TO_GROUP_TASKS' => $arResult['PATH_TO_GROUP_TASKS'],
	'PATH_TO_GROUP_CALENDAR' => $arResult['PATH_TO_GROUP_CALENDAR'],
	'PATH_TO_GROUP_FILES' => $arResult['PATH_TO_GROUP_FILES'],

	'PATH_TO_GROUP_FEATURES' => $arResult['PATH_TO_GROUP_FEATURES'],
	'PATH_TO_GROUP_USERS' => $arResult['PATH_TO_GROUP_USERS'],
	'PATH_TO_GROUP_INVITE' => $arResult['PATH_TO_GROUP_INVITE'],
];

if ($groupId)
{
	$componentParams['PATH_TO_SCRUM_TEAM_SPEED'] = $arResult['PATH_TO_SCRUM_TEAM_SPEED'];
	$componentParams['PATH_TO_SCRUM_BURN_DOWN'] = $arResult['PATH_TO_SCRUM_BURN_DOWN'];
	$componentParams['PATH_TO_GROUP_TASKS_TASK'] = $arResult['PATH_TO_GROUP_TASKS_TASK'];
}

$includeToolbar = true;