<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'DOMAIN_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_DOMAIN_ID'),
			'TYPE' => 'STRING'
		),
		'PAGE_URL_DOMAINS' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PAGE_URL_DOMAINS'),
			'TYPE' => 'STRING'
		)
	)
);
