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
	$types = [];
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
		'FOLDER_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_FOLDER_ID'),
			'TYPE' => 'STRING'
		),
		'LANDING_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_LANDING_ID'),
			'TYPE' => 'STRING'
		),
		'INPUT_VALUE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_INPUT_VALUE'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_LANDING_VIEW' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDING_VIEW'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_LANDING_ADD' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDING_ADD'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_FOLDER_ADD' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_FOLDER_ADD'),
			'TYPE' => 'STRING'
		)
	)
);
