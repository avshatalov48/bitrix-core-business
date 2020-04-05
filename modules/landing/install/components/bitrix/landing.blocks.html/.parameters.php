<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentParameters = array(
	'PARAMETERS' => array(
		'HTML_CODE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_HTML_CODE'),
			'TYPE' => 'STRING'
		)
	)
);

if (\Bitrix\Main\Config\Option::get('main', 'move_js_to_body') == 'Y')
{
	$arComponentParameters['PARAMETERS']['SKIP_MOVING_FALSE'] = array(
		'NAME' => getMessage('LANDING_CMP_PAR_SKIP_MOVING_FALSE'),
		'TYPE' => 'CHECKBOX'
	);
}