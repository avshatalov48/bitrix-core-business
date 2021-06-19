<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Loader::includeModule('sale'))
{
	return;
}

$arComponentParameters = [
	'PARAMETERS' => [
		'URL_PATH_TO_DETAIL_PRODUCT' => array(
			'NAME' => Loc::getMessage('SOC_URL_PATH_TO_DETAIL_PRODUCT'),
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'COLS' => 25,
			'PARENT' => 'ADDITIONAL_SETTINGS',
		),
	]
];
