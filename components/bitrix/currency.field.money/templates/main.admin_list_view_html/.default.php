<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Currency\UserField\Types\MoneyType;

$explode = MoneyType::unFormatFromDB($arResult['additionalParameters']['VALUE']);

$currentValue = $explode[0] ? $explode[0] : '';
$currentCurrency = $explode[1] ? $explode[1] : '';

if(!$currentCurrency)
{
	print ((int)$currentValue ? $currentValue : '');
}
else
{
	print CCurrencyLang::CurrencyFormat($currentValue, $currentCurrency, true);
}