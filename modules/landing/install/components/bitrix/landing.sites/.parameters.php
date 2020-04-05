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
		'PAGE_URL_SITE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITE'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_SITE_EDIT' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_SITE_EDIT'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_LANDING_EDIT' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDING_EDIT'),
			'TYPE' => 'STRING'
		),
		'SEF' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SEF'),
			'TYPE' => 'STRING',
			'MULTIPLE' => 'Y'
		),
		'TILE_MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_TILE_MODE'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'list',
			'VALUES' => [
				'list' => getMessage('LANDING_CMP_PAR_TILE_MODE_LIST'),
				'view' => getMessage('LANDING_CMP_PAR_TILE_MODE_VIEW')
			]
		),
		'DRAFT_MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_DRAFT_MODE'),
			'TYPE' => 'CHECKBOX'
		),
		'OVER_TITLE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_OVER_TITLE'),
			'TYPE' => 'STRING'
		),
	)
);
