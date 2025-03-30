<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentDescription = [
	'NAME' => GetMessage('CD_BSTC_NAME'),
	'DESCRIPTION' => GetMessage('CD_BSTC_DESCRIPTION'),
	'ICON' => '/images/search_form.gif',
	'CACHE_PATH' => 'Y',
	'PATH' => [
		'ID' => 'utility',
		'CHILD' => [
			'ID' => 'search',
			'NAME' => GetMessage('CD_BSTC_SEARCH')
		]
	],
];
