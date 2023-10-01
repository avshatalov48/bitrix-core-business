<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPIEBA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPIEBA_DESCR_DESCR'),
	'TYPE' => 'activity',
	'CLASS' => 'IfElseBranchActivity',
	'JSCLASS' => 'IfElseBranchActivity',
	'CATEGORY' => [],
	'SORT' => 100,
];
