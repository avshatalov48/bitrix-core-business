<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPCLDA_DESCR_NAME_1'),
	'DESCRIPTION' => Loc::getMessage('BPCLDA_DESCR_DESCR_1'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'CreateListsDocumentActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'document',
	],
	'RETURN' => [
		'ElementId' => [
			'NAME' => 'Id',
			'TYPE' => 'int',
		],
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee',
		'GROUP' => ['modificationData'],
		'SORT' => 4800,
	],
];