<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'AUTH_AUTH_URL' => array(
			'NAME' => Loc::getMessage('MAIN_AUTH_CHD_AUTH_AUTH_PASSWORD_URL'),
			'TYPE' => 'STRING'
		),
		'AUTH_REGISTER_URL' => array(
			'NAME' => Loc::getMessage('MAIN_AUTH_CHD_AUTH_REGISTER_URL'),
			'TYPE' => 'STRING'
		)
	)
);
