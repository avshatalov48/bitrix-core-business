<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

if (\Bitrix\Main\Loader::includeModule('landing'))
{
	$types = \Bitrix\Landing\Site::getTypes();
}
else
{
	$types = array();
}

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'SITE_TYPE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_LANDING_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $types
		),
		'LID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_LID'),
			'TYPE' => 'STRING'
		),
		'HTTP_HOST' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SERVER_NAME'),
			'TYPE' => 'STRING'
		),
		'PATH' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PATH'),
			'TYPE' => 'STRING'
		),
		'NOT_CHECK_DOMAIN' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_NOT_CHECK_DOMAIN'),
			'TYPE' => 'CHECKBOX'
		),
		'SHOW_EDIT_PANEL' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SHOW_EDIT_PANEL'),
			'TYPE' => 'CHECKBOX'
		),
		'DRAFT_MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_DRAFT_MODE'),
			'TYPE' => 'CHECKBOX'
		),
		'PAGE_URL_SITES' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITES'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_SITE_SHOW' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITE_SHOW'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_LANDING_VIEW' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDING_VIEW'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_ROLES' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_ROLES'),
			'TYPE' => 'STRING'
		)
	)
);
