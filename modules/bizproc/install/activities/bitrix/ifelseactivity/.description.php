<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPIEA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPIEA_DESCR_DESCR'),
	'TYPE' => 'activity',
	'CLASS' => 'IfElseActivity',
	'JSCLASS' => 'IfElseActivity',
	'CATEGORY' => [
		'ID' => 'logic',
	],
	'SORT' => 100,
];
