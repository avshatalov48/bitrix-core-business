<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPMA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPMA_DESCR_DESCR_1'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'MailActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'interaction',
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee',
		'TITLE' => Loc::getMessage('BPMA_DESCR_ROBOT_TITLE'),
		'RESPONSIBLE_PROPERTY' => 'MailUserToArray',
		'GROUP' => ['informingEmployee'],
		'SORT' => 1000,
	],
];