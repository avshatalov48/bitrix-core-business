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
		'PAGE_URL_LANDING_EDIT' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDING_EDIT'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_LANDING_VIEW' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDING_VIEW'),
			'TYPE' => 'STRING'
		),
		'SEF' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SEF'),
			'TYPE' => 'STRING'
		),
		'TILE_MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_TILE_MODE'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'edit',
			'VALUES' => [
				'edit' => getMessage('LANDING_CMP_PAR_TILE_MODE_EDIT'),
				'view' => getMessage('LANDING_CMP_PAR_TILE_MODE_VIEW')
			]
		),
		'DRAFT_MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_DRAFT_MODE'),
			'TYPE' => 'CHECKBOX'
		)
	)
);
