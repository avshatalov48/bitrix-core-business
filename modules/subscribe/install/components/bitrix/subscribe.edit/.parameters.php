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
			'NAME' => GetMessage('SUBSCR_SHOW_HIDDEN'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'ALLOW_ANONYMOUS' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('SUBSCR_ALLOW_ANONYMOUS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => COption::GetOptionString('subscribe', 'allow_anonymous'),
		],
		'SHOW_AUTH_LINKS' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('SUBSCR_SHOW_AUTH_LINKS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => COption::GetOptionString('subscribe', 'show_auth_links'),
		],
		'CACHE_TIME' => ['DEFAULT' => 3600],
		'SET_TITLE' => [],
	],
];
