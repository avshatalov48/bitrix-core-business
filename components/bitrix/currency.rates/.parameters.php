<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arCurrentValues */
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Currency;

if (!Loader::includeModule('currency'))
{
	return;
}

$arComponentParameters = [
	'PARAMETERS' => [
		'arrCURRENCY_FROM' => [
			'NAME' => Loc::getMessage('CURRENCY_FROM'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'ADDITIONAL_VALUES' => 'N',
			'VALUES' => Currency\CurrencyManager::getCurrencyList(),
			'GROUP' => 'BASE',
		],
		'CURRENCY_BASE' => [
			'NAME' => Loc::getMessage('CURRENCY_BASE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'VALUES' => Currency\CurrencyManager::getCurrencyList(),
			'DEFAULT' => Currency\CurrencyManager::getBaseCurrency(),
			'GROUP' => 'BASE',
		],
		'RATE_DAY' => [
			'NAME' => Loc::getMessage('CURRENCY_RATE_DAY'),
			'TYPE' => 'STRING',
			'GROUP' => 'ADDITIONAL_PARAMETERS',
		],
		'SHOW_CB' => [
			'NAME' => Loc::getMessage('T_CURRENCY_CBRF'),
			'TYPE' => 'CHECKBOX',
			'MULTIPLE' => 'N',
			'DEFAULT' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'GROUP' => 'ADDITIONAL_PARAMETERS',
		],
		'CACHE_TIME' => [
			'DEFAULT' => '86400',
		],
	],
];
