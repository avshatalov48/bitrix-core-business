<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'BLOCK_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_BLOCK_ID'),
			'TYPE' => 'STRING'
		),
		'DATA' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_DATA'),
			'TYPE' => 'STRING'
		),
		'REPLACE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_REPLACE'),
			'TYPE' => 'STRING'
		)
	)
);
