<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arActivityDescription = [
	'NAME' => GetMessage('BPTA1_DESCR_NAME'),
	'DESCRIPTION' => GetMessage('BPTA1_DESCR_DESCR'),
	'TYPE' => 'activity',
	'CLASS' => 'TerminateActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'other',
	],
];
