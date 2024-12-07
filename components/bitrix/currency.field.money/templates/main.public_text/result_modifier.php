<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Currency\UserField\Types\MoneyType;

/**
 * @var $arResult array
 */
$value = $arResult['value'];

$text = '';
$first = true;

$renderContext = ($arParams['additionalParameters']['renderContext'] ?? null);

foreach ($value as $res)
{
	if (!$first)
	{
		$text .= ', ';
	}

	$first = false;

	$explode = MoneyType::unformatFromDB($res);
	\CCurrencyLang::disableUseHideZero();
	$currentValue = \CCurrencyLang::CurrencyFormat($explode[0], $explode[1]);
	\CCurrencyLang::enableUseHideZero();

	if ($renderContext === 'export')
	{
		$currentValue = html_entity_decode($currentValue, ENT_NOQUOTES, LANG_CHARSET);
	}

	$text .= $currentValue;
}

$arResult['value'] = $text;
