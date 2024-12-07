<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentParameters = [
	'PARAMETERS' => [
		// 'SITE_TYPE' => array(
		// 	'NAME' => getMessage('LANDING_CMP_PAR_LANDING_TYPE'),
		// 	'TYPE' => 'LIST',
		// 	'VALUES' => $types
		// ),
		'LID' => [
			'NAME' => getMessage('LANDING_CMP_PAR_LID'),
			'TYPE' => 'STRING'
		],
		'PATH' => [
			'NAME' => getMessage('LANDING_CMP_PAR_PATH'),
			'TYPE' => 'STRING'
		],
		'SHOW_EDIT_PANEL' => [
			'NAME' => getMessage('LANDING_CMP_PAR_SHOW_EDIT_PANEL'),
			'TYPE' => 'CHECKBOX'
		],
		'DRAFT_MODE' => [
			'NAME' => getMessage('LANDING_CMP_PAR_DRAFT_MODE'),
			'TYPE' => 'CHECKBOX'
		],
		'PAGE_URL_SITES' => [
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITES'),
			'TYPE' => 'STRING'
		],
		'PAGE_URL_SITE_SHOW' => [
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITE_SHOW'),
			'TYPE' => 'STRING'
		],
		'PAGE_URL_LANDING_VIEW' => [
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDING_VIEW'),
			'TYPE' => 'STRING'
		],
		'PAGE_URL_ROLES' => [
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_ROLES'),
			'TYPE' => 'STRING'
		]
	]
];
