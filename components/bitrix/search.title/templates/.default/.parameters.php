<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
$arTemplateParameters = [
	'SHOW_INPUT' => [
		'NAME' => GetMessage('TP_BST_SHOW_INPUT'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
		'REFRESH' => 'Y',
	],
	'INPUT_ID' => [
		'NAME' => GetMessage('TP_BST_INPUT_ID'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'title-search-input',
	],
	'CONTAINER_ID' => [
		'NAME' => GetMessage('TP_BST_CONTAINER_ID'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'title-search',
	],
];
