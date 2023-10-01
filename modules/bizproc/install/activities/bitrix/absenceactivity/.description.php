<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arActivityDescription = [
	'NAME' => Bitrix\Main\Localization\Loc::getMessage('BPAA2_DESCR_NAME'),
	'DESCRIPTION' => Bitrix\Main\Localization\Loc::getMessage('BPAA2_DESCR_DESCR'),
	'TYPE' => 'activity',
	'CLASS' => 'AbsenceActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'interaction',
	],
];
