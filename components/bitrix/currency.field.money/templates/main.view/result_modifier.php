<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arResult
 * @var array $arParams
 */


use Bitrix\Currency\UserField\Types\MoneyType;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;

Extension::load([
	'uf',
]);

$arResult['currencies'] = CurrencyManager::getInstalledCurrencies();

$i = 0;
$asset = Asset::getInstance();

/** @var $component MoneyUfComponent */
$component = $this->getComponent();
$isMobileMode = $component->isMobileMode();
unset($component);

foreach ($arResult['value'] as $key => $rawValue)
{
	$explode = MoneyType::unFormatFromDB($rawValue);
	$currentValue = $explode[0];
	$currentCurrency = $explode[1];

	\CCurrencyLang::disableUseHideZero();
	$value = \CCurrencyLang::CurrencyFormat($currentValue, $currentCurrency);
	\CCurrencyLang::enableUseHideZero();

	$arResult['value'][$key] = [
		'value' => $value,
		'currentValue' => $currentValue,
		'currentCurrency' => $currentCurrency,
	];

	if ($isMobileMode)
	{
		$asset->addJs(
			'/bitrix/js/mobile/userfield/mobile_field.js'
		);
		$asset->addJs(
			'/bitrix/components/bitrix/currency.field.money/templates/main.view/mobile.js'
		);

		$attrList = [];
		$attrList['type'] = 'hidden';
		$attrList['data-bx-type'] = 'text';
		$attrList['placeholder'] = htmlspecialcharsbx(
			$arParams['userField']['placeholder'] ?: $arParams['userField']['name']
		);
		$attrList['name'] = $arResult['fieldName'];
		$attrList['id'] = $arParams['userField']['~id'] . '_' . $i++;
		$attrList['value'] = $rawValue;

		$arResult['value'][$key]['attrList'] = $attrList;
	}
}

unset($asset);
