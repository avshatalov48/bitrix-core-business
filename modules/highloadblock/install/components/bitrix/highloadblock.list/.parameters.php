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
			'NAME' => GetMessage('HLLIST_COMPONENT_BLOCK_ID_PARAM'),
			'TYPE' => 'TEXT',
		],
		'DETAIL_URL' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_DETAIL_URL_PARAM'),
			'TYPE' => 'TEXT',
		],
		'ROWS_PER_PAGE' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_ROWS_PER_PAGE_PARAM'),
			'TYPE' => 'TEXT',
		],
		'PAGEN_ID' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_PAGEN_ID_PARAM'),
			'TYPE' => 'TEXT',
			'DEFAULT' => 'page',
		],
		'FILTER_NAME' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_FILTER_NAME_PARAM'),
			'TYPE' => 'TEXT',
		],
		'SORT_FIELD' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_SORT_FIELD_PARAM'),
			'TYPE' => 'TEXT',
			'DEFAULT' => 'ID',
		],
		'SORT_ORDER' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_SORT_ORDER_PARAM'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'DESC',
			'VALUES' => [
				'DESC' => GetMessage('HLLIST_COMPONENT_SORT_ORDER_PARAM_DESC'),
				'ASC' => GetMessage('HLLIST_COMPONENT_SORT_ORDER_PARAM_ASC'),
			],
		],
		'CHECK_PERMISSIONS' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_CHECK_PERMISSIONS_PARAM'),
			'TYPE' => 'CHECKBOX',
		],
	],
];
