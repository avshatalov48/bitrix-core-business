<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPDDA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPDDA_DESCR_DESCR_1'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'DeleteDocumentActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'document',
	],
	'FILTER' => [
		'EXCLUDE' => [
			['tasks'],
		],
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee',
		'TITLE' => Loc::getMessage('BPDDA_DESCR_ROBOT_TITLE_1'),
		'GROUP' => ['elementControl'],
		'SORT' => 2900,
	],
];