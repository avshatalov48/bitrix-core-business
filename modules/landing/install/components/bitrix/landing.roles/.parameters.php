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
			'NAME' => getMessage('LANDING_CMP_PAR_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $types
		),
		'PAGE_URL_ROLE_EDIT' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_ROLE_EDIT'),
			'TYPE' => 'STRING'
		)
	)
);
