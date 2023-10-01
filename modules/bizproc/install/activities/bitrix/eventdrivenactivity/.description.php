<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPEDA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPEDA_DESCR_DESCR_1'),
	'TYPE' => 'activity',
	'CLASS' => 'EventDrivenActivity',
	'JSCLASS' => 'EventDrivenActivity',
	'CATEGORY' => [],
	'SORT' => 100,
];
