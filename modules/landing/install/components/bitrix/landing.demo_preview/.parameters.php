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
		'SITE_WORK_MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SITE_WORK_MODE'),
			'TYPE' => 'CHECKBOX'
		),
		'CODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_CODE'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_BACK' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_BACK'),
			'TYPE' => 'STRING'
		)
	)
);
