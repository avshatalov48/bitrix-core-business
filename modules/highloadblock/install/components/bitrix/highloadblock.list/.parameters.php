<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	'GROUPS' => array(
	),
	'PARAMETERS' => array(
		'BLOCK_ID' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_BLOCK_ID_PARAM'),
			'TYPE' => 'TEXT'
		),
		'DETAIL_URL' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_DETAIL_URL_PARAM'),
			'TYPE' => 'TEXT'
		),
		'ROWS_PER_PAGE' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_ROWS_PER_PAGE_PARAM'),
			'TYPE' => 'TEXT'
		),
		'PAGEN_ID' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_PAGEN_ID_PARAM'),
			'TYPE' => 'TEXT',
			'DEFAULT' => 'page'
		),
		'FILTER_NAME' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_FILTER_NAME_PARAM'),
			'TYPE' => 'TEXT'
		),
		'SORT_FIELD' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_SORT_FIELD_PARAM'),
			'TYPE' => 'TEXT',
			'DEFAULT' => 'ID'
		),
		'SORT_ORDER' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_SORT_ORDER_PARAM'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'DESC',
			'VALUES' => array(
				'DESC' => GetMessage('HLLIST_COMPONENT_SORT_ORDER_PARAM_DESC'),
				'ASC' => GetMessage('HLLIST_COMPONENT_SORT_ORDER_PARAM_ASC')
			)
		),
		'CHECK_PERMISSIONS' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('HLLIST_COMPONENT_CHECK_PERMISSIONS_PARAM'),
			'TYPE' => 'CHECKBOX'
		),
	),
);