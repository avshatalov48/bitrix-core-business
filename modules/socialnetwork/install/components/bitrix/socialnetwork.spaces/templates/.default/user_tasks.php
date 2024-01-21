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
/** @var array $componentParams */

use Bitrix\Intranet\Integration\Wizards\Portal\Ids;

require_once __DIR__ . '/params.php';

$componentParams = array_merge(
	$componentParams,
	[
		'PAGE' => 'user_tasks',
		'PAGE_TYPE' => 'user',
		'PAGE_ID' => 'tasks',
	],
);

$listComponentParams = array_merge(
	$componentParams,
	[

	],
);

$menuComponentParams = array_merge(
	$componentParams,
	[

	],
);

$toolbarComponentParams = array_merge(
	$componentParams,
	[
		'USE_LIVE_SEARCH' => 'Y',
		'PATH_TO_USER_TASKS_TASK' => $arResult['PATH_TO_USER_TASKS_TASK'],
		'PATH_TO_GROUP_TASKS_TASK' => $arResult['PATH_TO_GROUP_TASKS_TASK'],
	],
);

$contentComponentParams = array_merge(
	$componentParams,
	[
		'TASK_FORUM_ID' => Ids::getForumId('intranet_tasks'),
		'DATE_TIME_FORMAT' => $arResult['DATE_TIME_FORMAT'],
		'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
		'SHOW_LOGIN' => 'Y',
		'CACHE_TIME' => 3600,
		'HIDE_OWNER_IN_TITLE' => 'Y',
		'SET_NAV_CHAIN' => 'Y',
		'SHOW_YEAR' => 'Y',

		'PATH_TO_MESSAGES_CHAT' => $arResult['PATH_TO_MESSAGES_CHAT'],
		'PATH_TO_COMPANY_DEPARTMENT' => $arResult['PATH_TO_COMPANY_DEPARTMENT'],
		'PATH_TO_VIDEO_CALL' => $arResult['PATH_TO_VIDEO_CALL'],

		'PATH_TO_USER_TASKS_TASK' => $arResult['PATH_TO_USER_TASKS_TASK'],
		'PATH_TO_USER_TASKS_VIEW' => $arResult['PATH_TO_USER_TASKS_VIEW'],
		'PATH_TO_USER_TASKS_REPORT' => $arResult['PATH_TO_USER_TASKS_REPORT'],
		'PATH_TO_USER_TASKS_TEMPLATES' => $arResult['PATH_TO_USER_TASKS_TEMPLATES'],

		'PATH_TO_GROUP_TASKS_TASK' => $arResult['PATH_TO_GROUP_TASKS_TASK'],
		'PATH_TO_GROUP_TASKS_VIEW' => $arResult['PATH_TO_GROUP_TASKS_VIEW'],
		'PATH_TO_GROUP_TASKS_REPORT' => $arResult['PATH_TO_GROUP_TASKS_REPORT'],
	],
);

require_once __DIR__ . '/template.php';
