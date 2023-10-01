<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPC_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPC_DESCR_DESCR'),
	'TYPE' => 'condition',
	'FILTER' => [
		'EXCLUDE' => CBPHelper::DISTR_B24,
	],
];
