<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'AUTH_FORGOT_PASSWORD_URL' => array(
			'NAME' => Loc::getMessage('MAIN_AUTH_FORM_AUTH_FORGOT_PASSWORD_URL'),
			'TYPE' => 'STRING'
		),
		'AUTH_REGISTER_URL' => array(
			'NAME' => Loc::getMessage('MAIN_AUTH_FORM_AUTH_REGISTER_URL'),
			'TYPE' => 'STRING'
		),
		'AUTH_SUCCESS_URL' => array(
			'NAME' => Loc::getMessage('MAIN_AUTH_FORM_AUTH_SUCCESS_URL'),
			'TYPE' => 'STRING'
		)
	)
);
