<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BP_SSA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BP_SSA_DESCR_DESCR'),
	'TYPE' => ['activity'],
	'CLASS' => 'StartScriptActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'document',
	],
	'FILTER' => [
		'INCLUDE' => [
			['crm'],
		],
	],
];