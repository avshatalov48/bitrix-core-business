<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentDescription = [
	'NAME' => GetMessage('SEARCH_SEARCH_PAGE_NAME'),
	'DESCRIPTION' => GetMessage('SEARCH_SEARCH_PAGE_DESCRIPTION'),
	'ICON' => '/images/search_page.gif',
	'CACHE_PATH' => 'Y',
	'PATH' => [
		'ID' => 'utility',
		'CHILD' => [
			'ID' => 'search',
			'NAME' => GetMessage('SEARCH_SERVICE')
		]
	],
];
