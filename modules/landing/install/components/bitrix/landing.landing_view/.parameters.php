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
		'TYPE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_LANDING_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $types
		),
		'SITE_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SITE_ID'),
			'TYPE' => 'STRING'
		),
		'LANDING_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_LANDING_ID'),
			'TYPE' => 'STRING'
		),
		'PANEL_LIGHT_MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PANEL_LIGHT_MODE'),
			'TYPE' => 'CHECKBOX'
		),
		'FULL_PUBLICATION' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_FULL_PUBLICATION'),
			'TYPE' => 'CHECKBOX'
		),
		'DONT_LEAVE_AFTER_PUBLICATION' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_DONT_LEAVE_AFTER_PUBLICATION'),
			'TYPE' => 'CHECKBOX'
		),
		'DRAFT_MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_DRAFT_MODE'),
			'TYPE' => 'CHECKBOX'
		),
		'PAGE_URL_URL_SITES' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_URL_SITES'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_LANDINGS' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDINGS'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_LANDING_EDIT' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDING_EDIT'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_SITE_EDIT' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITE_EDIT'),
			'TYPE' => 'STRING'
		),
		'PARAMS' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PARAMS'),
			'TYPE' => 'STRING'
		),
		'SEF' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SEF'),
			'TYPE' => 'STRING'
		)
	)
);
