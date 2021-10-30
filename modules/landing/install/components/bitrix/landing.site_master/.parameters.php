<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Landing\Site;
use Bitrix\Main\Loader;

if (Loader::includeModule('landing'))
{
	$types = Site::getTypes();
}
else
{
	$types = array();
}

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'TYPE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SITE_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $types
		),
		'SITE_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SITE_ID'),
			'TYPE' => 'STRING'
		),
		'GET_DATA' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_GET_DATA'),
			'TYPE' => 'CHECKBOX'
		),
		'PAGE_URL_SITE_MASTER' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITE_MASTER'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_LANDING_VIEW' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDING_VIEW'),
			'TYPE' => 'STRING'
		)
	)
);