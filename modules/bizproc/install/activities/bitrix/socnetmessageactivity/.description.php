<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPSNMA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPSNMA_DESCR_DESCR_1'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'SocNetMessageActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'interaction',
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee',
		'TITLE' => Loc::getMessage('BPSNMA_DESCR_ROBOT_TITLE_1'),
		'RESPONSIBLE_PROPERTY' => 'MessageUserTo',
		'GROUP' => ['informingEmployee'],
		'SORT' => 700,
	],
];