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
	'core_uf_money',
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
	$arResult['value'][$key] = [
		'value' => $rawValue,
		'currentValue' => $explode[0],
		'currentCurrency' => $explode[1],
	];
	unset($explode);

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
		$attrList['name'] = str_replace('[]', '[' . $key . ']', $arResult['fieldName']);
		$attrList['id'] = $arParams['userField']['~id'] . '_' . $i++;
		$attrList['value'] = $rawValue;
		$attrList['data-user-field-type-name'] = 'BX.Mobile.Field.Money';
		$arResult['value'][$key]['attrList'] = $attrList;
	}
}

unset($asset);
