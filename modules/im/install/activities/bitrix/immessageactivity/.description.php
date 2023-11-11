<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPIMMA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPIMMA_DESCR_DESCR'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'ImMessageActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'interaction',
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee',
		'RESPONSIBLE_PROPERTY' => 'Responsible',
		'GROUP' => ['informingEmployee'],
		'SORT' => 982,
	],
];
