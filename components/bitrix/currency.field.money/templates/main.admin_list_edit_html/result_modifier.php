<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

use Bitrix\Currency\Helpers\Editor;
use Bitrix\Currency\UserField\Types\MoneyType;

$currencyList = Editor::getListCurrency();
$defaultCurrency = '';

foreach ($currencyList as $currency => $currencyInfo)
{
	if ($defaultCurrency === '' || $currencyInfo['BASE'] === 'Y')
	{
		$defaultCurrency = $currency;
	}

	$arResult['CURRENCY_LIST'][$currency] = $currencyInfo['NAME'];
}

$arResult['VALUE_NUMBER'] = '';
$arResult['VALUE_CURRENCY'] = '';
if (!empty($arResult['additionalParameters']['VALUE']))
{
	$explode = MoneyType::unFormatFromDb($arResult['additionalParameters']['VALUE']);
	$arResult['VALUE_NUMBER'] = htmlspecialcharsbx($explode[0]);
	$arResult['VALUE_CURRENCY'] = htmlspecialcharsbx($explode[1]);
}
