<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Page\Asset;
use Bitrix\Currency\UserField\Types\MoneyType;
use Bitrix\Currency\CurrencyManager;

CJSCore::init(['uf', 'core_uf_money']);

$arResult['currencies'] = CurrencyManager::getInstalledCurrencies();

$i = 0;

foreach($arResult['value'] as $key => $rawValue)
{
	$explode = MoneyType::unFormatFromDB($rawValue);
	$currentValue = $value = ($explode[0] <> ''? (float)$explode[0] : '');
	$currentCurrency = $explode[1] ?: '';

	$format = \CCurrencyLang::GetFormatDescription($currentCurrency);

	$arResult['value'][$key] = [
		'value' => $rawValue,
		'currentValue' => $currentValue,
		'currentCurrency' => $currentCurrency,
	];

	/**
	 * @var $component MoneyUfComponent
	 */

	$component = $this->getComponent();
	if($component->isMobileMode())
	{
		Asset::getInstance()->addJs(
			'/bitrix/js/mobile/userfield/mobile_field.js'
		);
		Asset::getInstance()->addJs(
			'/bitrix/components/bitrix/currency.field.money/templates/main.view/mobile.js'
		);

		$attrList = [];
		$attrList['type'] = 'hidden';
		$attrList['data-bx-type'] = 'text';
		$attrList['placeholder'] = htmlspecialcharsbx(
			$arParams['userField']['placeholder'] ?: $arParams['userField']['name']
		);
		$attrList['name'] = str_replace('[]', '[' . $key . ']', $arResult['fieldName']);
		$attrList['id'] = $arParams['userField']['~id'] . '_' . $i++;
		$attrList['value'] = $rawValue;
		$attrList['data-user-field-type-name'] = 'BX.Mobile.Field.Money';
		$arResult['value'][$key]['attrList'] = $attrList;
	}

}
