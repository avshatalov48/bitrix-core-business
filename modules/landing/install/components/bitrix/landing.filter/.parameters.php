<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'FILTER_TYPE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_FILTER_TYPE'),
			'TYPE' => 'STRING'
		),
		'SETTING_LINK' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_SETTING_LINK'),
			'TYPE' => 'STRING'
		),
		'FOLDER_LID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_FOLDER_LID'),
			'TYPE' => 'STRING'
		),
		'BUTTONS' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_BUTTONS'),
			'TYPE' => 'LIST'
		)
	)
);
