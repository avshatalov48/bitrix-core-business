<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arTemplateParameters = [
	'PAGE_ELEMENTS' => [
		'NAME' => GetMessage('SEARCH_PAGE_ELEMENTS'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => '10'
	],
	'SORT_BY_CNT' => [
		'NAME' => GetMessage('SEARCH_SORT_BY_CNT'),
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'DEFAULT' => 'Y'
	],
	'TEXT' => [
		'NAME' => GetMessage('SEARCH_TEXT'),
		'TYPE' => 'STRING',
		'MULTIPLE' => 'N',
		'DEFAULT' => ''
	],
	'TMPL_IFRAME' => [
		'NAME' => GetMessage('SEARCH_SHOW_IFRAME'),
		'TYPE' => 'CHECKBOX',
		'MULTIPLE' => 'N',
		'DEFAULT' => 'Y'
	],
];
