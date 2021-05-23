<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'TYPE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SITE_TYPE'),
			'TYPE' => 'STRING'
		),
		'DOMAIN_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_DOMAIN_ID'),
			'TYPE' => 'STRING'
		),
		'FIELD_NAME' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_FIELD_NAME'),
			'TYPE' => 'STRING'
		),
		'FIELD_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_FIELD_ID'),
			'TYPE' => 'STRING'
		)
	)
);
