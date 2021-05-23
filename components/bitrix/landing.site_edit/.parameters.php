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
		'TEMPLATE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_TEMPLATE'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_SITES' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITES'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_LANDING_VIEW' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDING_VIEW'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_SITE_DOMAIN' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITE_DOMAIN'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_SITE_COOKIES' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITE_COOKIES'),
			'TYPE' => 'STRING'
		)
	)
);
