<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentDescription = [
	'NAME' => GetMessage('SEARCH_FORM_NAME'),
	'DESCRIPTION' => GetMessage('SEARCH_FORM_DESC'),
	'ICON' => '/images/search_form.gif',
	'CACHE_PATH' => 'Y',
	'PATH' => [
		'ID' => 'utility',
		'CHILD' => [
			'ID' => 'search',
			'NAME' => GetMessage('SEARCH_SERVICE')
		]
	],
];
