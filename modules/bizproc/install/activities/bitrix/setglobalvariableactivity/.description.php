<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arActivityDescription = [
	'NAME' => GetMessage('BPSGVA_DESCR_NAME'),
	'DESCRIPTION' => GetMessage('BPSGVA_DESCR_DESCR'),
	'TYPE' => ['activity'],
	'CLASS' => 'SetGlobalVariableActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'other',
	],
	'ROBOT_SETTINGS' => [
		'TITLE' => GetMessage('BPSGVA_DESCR_ROBOT_TITLE_1'),
		'CATEGORY' => 'employee'
	],
];
