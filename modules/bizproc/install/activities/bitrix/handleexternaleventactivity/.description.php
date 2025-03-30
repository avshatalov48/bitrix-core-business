<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPHEEA_DESCR_NAME_MSGVER_1'),
	'DESCRIPTION' => Loc::getMessage('BPHEEA_DESCR_DESCR_MSGVER_1'),
	'TYPE' => 'activity',
	'CLASS' => 'HandleExternalEventActivity',
	'JSCLASS' => 'HandleExternalEventActivity',
	'CATEGORY' => [
		'ID' => 'logic',
	],
	'RETURN' => [
		'SenderUserId' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_SENDER_USER_ID_MSGVER_1'),
			'TYPE' => 'user',
		],
	],
];
