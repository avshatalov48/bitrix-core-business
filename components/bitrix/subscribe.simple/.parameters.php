<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentParameters = [
	'GROUPS' => [
	],
	'PARAMETERS' => [
		'AJAX_MODE' => [],
		'SHOW_HIDDEN' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_BSS_SHOW_HIDDEN'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'CACHE_TIME'  => ['DEFAULT' => 3600],
		'SET_TITLE' => [],
	],
];
