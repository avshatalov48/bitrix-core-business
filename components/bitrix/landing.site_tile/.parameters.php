<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
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
	$types = [];
}

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'TYPE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_LANDING_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $types
		),
		'FEEDBACK_CODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_FEEDBACK_CODE'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_SITE_ADD' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITE_ADD'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_SITE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITE'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_DOMAIN' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_DOMAIN'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_CONTACTS' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_CONTACTS'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_SITE_DOMAIN_SWITCH' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITE_DOMAIN_SWITCH'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_CRM_ORDERS' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_CRM_ORDERS'),
			'TYPE' => 'STRING'
		)
	)
);
