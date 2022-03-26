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
			'NAME' => getMessage('LANDING_CMP_PAR_TYPE'),
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
		'PAGE_URL_LANDINGS' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDINGS'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_LANDING_VIEW' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDING_VIEW'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_SITE_EDIT' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITE_EDIT'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_FOLDER_EDIT' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_FOLDER_EDIT'),
			'TYPE' => 'STRING'
		)
	)
);
