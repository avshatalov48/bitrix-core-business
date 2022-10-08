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
		'URL_PATH_TO_DETAIL_PRODUCT' => [
			'NAME' => Loc::getMessage('SOC_URL_PATH_TO_DETAIL_PRODUCT'),
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'COLS' => 25,
			'PARENT' => 'ADDITIONAL_SETTINGS',
		],
		'SHOW_RETURN_BUTTON' => [
			'NAME' => Loc::getMessage('SOC_SHOW_RETURN_BUTTON'),
			'TYPE' => 'CHECKBOX',
			'MULTIPLE' => 'N',
			'DEFAULT' => 'Y',
			'ADDITIONAL_VALUES' => 'N',
			'PARENT' => 'ADDITIONAL_SETTINGS',
		],
	]
];
