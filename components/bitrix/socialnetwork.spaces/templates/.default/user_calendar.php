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

require_once __DIR__ . '/params.php';

$componentParams = array_merge(
	$componentParams,
	[
		'PAGE' => 'user_calendar',
		'PAGE_TYPE' => 'user',
		'PAGE_ID' => 'calendar',
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

	],
);

$contentComponentParams = array_merge(
	$componentParams,
	[
		'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
		'CALENDAR_ALLOW_SUPERPOSE' => 'Y',
		'CALENDAR_ALLOW_RES_MEETING' => 'Y',
		'HIDE_OWNER_IN_TITLE' => 'Y',

		'PATH_TO_COMPANY_DEPARTMENT' => $arResult['PATH_TO_COMPANY_DEPARTMENT'],
		'PATH_TO_USER_TASKS_TASK' => $arResult['PATH_TO_USER_TASKS_TASK'],
		'PATH_TO_GROUP_TASKS_TASK' => $arResult['PATH_TO_GROUP_TASKS_TASK'],
	],
);

require_once __DIR__ . '/template.php';
