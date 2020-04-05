<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'SITE_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SITE_ID'),
			'TYPE' => 'STRING'
		),
		'LANDING_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_LANDING_ID'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_LANDINGS' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDINGS'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_LANDING_VIEW' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_LANDING_VIEW'),
			'TYPE' => 'STRING'
		)
	)
);
