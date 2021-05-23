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
		'MODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_LANDING_MODE'),
			'TYPE' => 'LIST',
			'VALUES' => [
				'LIST' => getMessage('LANDING_CMP_PAR_LANDING_MODE_LIST'),
				'CREATE' => getMessage('LANDING_CMP_PAR_LANDING_MODE_CREATE')
			]
		),
		'MENU_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_MENU_ID'),
			'TYPE' => 'STRING'
		),
		'SITE_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SITE_ID'),
			'TYPE' => 'STRING'
		),
		'PATH_AFTER_CREATE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PATH_AFTER_CREATE'),
			'TYPE' => 'STRING'
		)
	)
);
