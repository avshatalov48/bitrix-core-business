<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPSFA_DESCR_NAME_MGVER_1'),
	'DESCRIPTION' => Loc::getMessage('BPSFA_DESCR_DESCR_1'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'SetFieldActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'document',
	],
	'RETURN' => [
		'ErrorMessage' => [
			'NAME' => Loc::getMessage('BPSFA_DESCR_ERROR_MESSAGE'),
			'TYPE' => 'string',
		],
	],
	'FILTER' => [
		'EXCLUDE' => [
			['tasks'],
		],
	],
	'ROBOT_SETTINGS' => [
		'TITLE' => Loc::getMessage('BPSFA_DESCR_ROBOT_TITLE_2'),
		'CATEGORY' => 'employee',
		'GROUP' => ['elementControl'],
		'ASSOCIATED_TRIGGERS' => [
			'FIELD_CHANGED' => 1,
		],
		'SORT' => 2400,
	],
];