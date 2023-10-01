<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPCA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPCA_DESCR_DESCR'),
	'TYPE' => 'activity',
	'CLASS' => 'CodeActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'other',
	],
	'FILTER' => [
		'EXCLUDE' => CBPHelper::DISTR_B24,
	],
];

