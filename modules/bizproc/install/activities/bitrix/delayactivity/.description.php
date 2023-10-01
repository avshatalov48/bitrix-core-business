<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPDA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPDA_DESCR_DESCR_1'),
	'TYPE' => 'activity',
	'CLASS' => 'DelayActivity',
	'JSCLASS' => 'DelayActivity',
	'CATEGORY' => [
		'ID' => 'other',
	],
];

