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
		'DONT_LEAVE_FRAME' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_DONT_LEAVE_FRAME'),
			'TYPE' => 'CHECKBOX'
		),
		'BINDING_TYPE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_BINDING_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => [
				'GROUP' => getMessage('LANDING_CMP_PAR_BINDING_TYPE_GROUP'),
				'MENU' => getMessage('LANDING_CMP_PAR_BINDING_TYPE_MENU')
			]
		),
		'BINDING_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_BINDING_ID'),
			'TYPE' => 'STRING'
		)
	)
);
