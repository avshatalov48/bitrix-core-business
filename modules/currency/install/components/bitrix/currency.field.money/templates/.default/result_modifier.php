<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

use Bitrix\Currency\UserField\Types\MoneyType;
use Bitrix\Currency\CurrencyManager;

$arResult['currencies'] = CurrencyManager::getInstalledCurrencies();

foreach ($arResult['value'] as $key => $rawValue)
{
	$explode = MoneyType::unFormatFromDB($rawValue);

	\CCurrencyLang::disableUseHideZero();
	$arResult['value'][$key] = \CCurrencyLang::CurrencyFormat($explode[0], $explode[1]);
	\CCurrencyLang::enableUseHideZero();
}
