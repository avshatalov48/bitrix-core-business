<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arComponentDescription = array(
	'NAME' => Loc::getMessage('MAIN_AUTH_FORM_TITLE'),
	'DESCRIPTION' => Loc::getMessage('MAIN_AUTH_FORM_DESCR'),
	'PATH' => array(
		'ID' => 'utility',
		'CHILD' => array(
			'ID' => 'user',
			'NAME' => Loc::getMessage('MAIN_AUTH_FORM_GROUP_NAME')
		),
	),
);