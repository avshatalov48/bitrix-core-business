<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentDescription = [
	'NAME' => GetMessage('CD_BSS_NAME'),
	'DESCRIPTION' => GetMessage('CD_BSS_DESCRIPTION'),
	'ICON' => '/images/subscr_edit.gif',
	'CACHE_PATH' => 'Y',
	'PATH' => [
		'ID' => 'service',
		'CHILD' => [
			'ID' => 'subscribe',
			'NAME' => GetMessage('CD_BSS_SERVICE')
		],
	],
];
