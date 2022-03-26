<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arActivityDescription = [
	'NAME' => GetMessage('BPMOA_DESCR_NAME'),
	'DESCRIPTION' => GetMessage('BPMOA_DESCR_DESCR'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'MathOperationActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'other',
	],
	'ROBOT_SETTINGS' => [
		'TITLE' => GetMessage('BPMOA_DESCR_ROBOT_TITLE'),
		'CATEGORY' => 'employee',
	],
];
