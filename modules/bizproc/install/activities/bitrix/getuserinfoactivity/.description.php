<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPGUIA_DESCR_NAME_1'),
	'DESCRIPTION' => Loc::getMessage('BPGUIA_DESCR_DESCR_1'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'GetUserInfoActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'other',
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee',
		'GROUP' => ['other'],
		'SORT' => 3400,
		'IS_SUPPORTING_ROBOT' => true,
	],
	'ADDITIONAL_RESULT' => ['UserFields'],
];