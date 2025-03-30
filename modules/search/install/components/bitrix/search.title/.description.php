<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentDescription = [
	'NAME' => GetMessage('CD_BST_NAME'),
	'DESCRIPTION' => GetMessage('CD_BST_DESCRIPTION'),
	'ICON' => '/images/search_title.gif',
	'CACHE_PATH' => 'Y',
	'PATH' => [
		'ID' => 'utility',
		'CHILD' => [
			'ID' => 'search',
			'NAME' => GetMessage('CD_BST_SEARCH')
		]
	],
];
