<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentDescription = [
	'NAME' => GetMessage('CD_BSN_NAME'),
	'DESCRIPTION' => GetMessage('CD_BSN_DESCRIPTION'),
	'ICON' => '/images/subscr_news_list.gif',
	'CACHE_PATH' => 'Y',
	'PATH' => [
		'ID' => 'service',
		'CHILD' => [
			'ID' => 'subscribe',
			'NAME' => GetMessage('CD_BSN_SERVICE')
		],
	],
];
