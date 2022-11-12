<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPSGVA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPSGVA_DESCR_DESCR_1'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'SetGlobalVariableActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'other',
	],
	'ROBOT_SETTINGS' => [
		'TITLE' => Loc::getMessage('BPSGVA_DESCR_ROBOT_TITLE_2'),
		'CATEGORY' => 'employee',
		'GROUP' => ['modificationData'],
		'SORT' => 5200,
	],
];
