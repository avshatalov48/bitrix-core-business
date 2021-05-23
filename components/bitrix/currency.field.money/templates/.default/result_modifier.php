<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Currency\UserField\Types\MoneyType;
use Bitrix\Currency\CurrencyManager;

$arResult['currencies'] = CurrencyManager::getInstalledCurrencies();

foreach($arResult['value'] as $key => $rawValue)
{
	$explode = MoneyType::unFormatFromDB($rawValue);
	$value = ($explode[0] <> ''? (float)$explode[0] : '');
	$currency = ($explode[1] ?: '');

	$format = \CCurrencyLang::GetFormatDescription($currency);

	$value = number_format(
		(float)$value,
		$format['DECIMALS'],
		$format['DEC_POINT'],
		$format['THOUSANDS_SEP']
	);
	$value = \CCurrencyLang::applyTemplate($value, $format['FORMAT_STRING']);

	$arResult['value'][$key] = $value;
}
