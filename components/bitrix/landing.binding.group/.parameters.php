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
		'GROUP_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_GROUP_ID'),
			'TYPE' => 'STRING'
		),
		'PATH_AFTER_CREATE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PATH_AFTER_CREATE'),
			'TYPE' => 'STRING'
		)
	)
);
