<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentParameters = [
	'GROUPS' => [
	],
	'PARAMETERS' => [
		'SHOW_COUNT' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('SUBSCR_SHOW_COUNT'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'SHOW_HIDDEN' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('SUBSCR_SHOW_HIDDEN'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'PAGE' => [
			'PARENT' => 'URL_TEMPLATES',
			'NAME' => GetMessage('SUBSCR_FORM_PAGE'),
			'TYPE' => 'STRING',
			'DEFAULT' => COption::GetOptionString('subscribe', 'subscribe_section').'subscr_edit.php',
		],
		'CACHE_TIME' => ['DEFAULT' => 3600],
		'SET_TITLE' => [],
	],
];
