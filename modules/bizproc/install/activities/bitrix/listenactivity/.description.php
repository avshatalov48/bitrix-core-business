<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPLA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPLA_DESCR_DESCR'),
	'TYPE' => 'activity',
	'CLASS' => 'ListenActivity',
	'JSCLASS' => 'ListenActivity',
	'CATEGORY' => [
		'ID' => 'logic',
	],
	'SORT' => 100,
];
