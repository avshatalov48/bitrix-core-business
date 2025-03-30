<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
$arTemplateParameters = [
	'FONT_MAX' => [
		'NAME' => GetMessage('SEARCH_FONT_MAX'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => '50'
	],
	'FONT_MIN' => [
		'NAME' => GetMessage('SEARCH_FONT_MIN'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => '10'
	],
	'COLOR_NEW' => [
		'NAME' => GetMessage('SEARCH_COLOR_NEW'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => '3E74E6'
	],
	'COLOR_OLD' => [
		'NAME' => GetMessage('SEARCH_COLOR_OLD'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => 'C0C0C0'
	],
	'PERIOD_NEW_TAGS' => [
		'NAME' => GetMessage('SEARCH_PERIOD_NEW_TAGS'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => ''
	],
	'SHOW_CHAIN' => [
		'NAME' => GetMessage('SEARCH_SHOW_CHAIN'),
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'DEFAULT' => 'Y',
	],
	'COLOR_TYPE' => [
		'NAME' => GetMessage('SEARCH_COLOR_TYPE'),
		'TYPE' => 'LIST',
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'DEFAULT' => 'Y',
	],
	'WIDTH' => [
		'NAME' => GetMessage('SEARCH_WIDTH'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => '100%'
	],
];
