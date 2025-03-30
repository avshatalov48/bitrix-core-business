<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentDescription = [
	'NAME' => GetMessage('CD_BSSI_NAME'),
	'DESCRIPTION' => GetMessage('CD_BSSI_DESCRIPTION'),
	'ICON' => '/images/suggest.gif',
	'CACHE_PATH' => 'Y',
	'PATH' => [
		'ID' => 'utility',
		'CHILD' => [
			'ID' => 'search',
			'NAME' => GetMessage('CD_BSSI_SEARCH_SERVICE')
		]
	],
];
