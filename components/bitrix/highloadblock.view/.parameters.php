<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentParameters = [
	'GROUPS' => [],
	'PARAMETERS' => [
		'BLOCK_ID' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLVIEW_COMPONENT_BLOCK_ID_PARAM'),
			'TYPE' => 'TEXT',
			'DEFAULT' => '={$_REQUEST[\'BLOCK_ID\']}',
		],
		'ROW_KEY' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLVIEW_COMPONENT_KEY_PARAM'),
			'TYPE' => 'TEXT',
			'DEFAULT' => 'ID',
		],
		'ROW_ID' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLVIEW_COMPONENT_ID_PARAM'),
			'TYPE' => 'TEXT',
			'DEFAULT' => '={$_REQUEST[\'ID\']}',
		],
		'LIST_URL' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLVIEW_COMPONENT_LIST_URL_PARAM'),
			'TYPE' => 'TEXT',
		],
		'CHECK_PERMISSIONS' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLVIEW_COMPONENT_CHECK_PERMISSIONS_PARAM'),
			'TYPE' => 'CHECKBOX',
		],
	],
];
