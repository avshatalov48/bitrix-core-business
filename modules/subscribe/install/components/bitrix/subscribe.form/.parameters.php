<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentParameters = [
	'GROUPS' => [
	],
	'PARAMETERS' => [
		'USE_PERSONALIZATION' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BSF_USE_PERSONALIZATION'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'PAGE' => [
			'PARENT' => 'URL_TEMPLATES',
			'NAME' => GetMessage('CP_BSF_PAGE'),
			'TYPE' => 'STRING',
			'DEFAULT' => COption::GetOptionString('subscribe', 'subscribe_section') . 'subscr_edit.php',
		],
		'SHOW_HIDDEN' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_BSF_SHOW_HIDDEN'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'CACHE_TIME' => ['DEFAULT' => 3600],
	],
];
