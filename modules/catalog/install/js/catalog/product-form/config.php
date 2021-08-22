<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
$currencyId = '';
$currencySymbol = '';
if (\Bitrix\Main\Loader::includeModule('currency'))
{
	$currencyId = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
	$format = \CCurrencyLang::GetFormatDescription($currencyId);
	$currencySymbol = isset($format['FORMAT_STRING']) ? trim(\CCurrencyLang::applyTemplate('', $format['FORMAT_STRING'])) : '';
}

$basePriceId = null;
$hasLandingStore = false;
$isEnabledLanding = false;
$isLimitedLanding = false;
if (\Bitrix\Main\Loader::includeModule('catalog'))
{
	$baseGroup = \CCatalogGroup::GetBaseGroup();
	$basePriceId = (is_array($baseGroup) && isset($baseGroup['ID'])) ? (int)$baseGroup['ID'] : null;

	if (\Bitrix\Main\Loader::includeModule('landing'))
	{
		$isEnabledLanding = true;
		$hasLandingStore = Bitrix\Catalog\v2\Integration\Landing\StoreV3Master::hasStore();
		$isLimitedLanding = !$hasLandingStore && !Bitrix\Catalog\v2\Integration\Landing\StoreV3Master::canCreate();
	}
}

return [
	'css' => 'dist/product-form.bundle.css',
	'js' => 'dist/product-form.bundle.js',
	'rel' => [
		'currency',
		'ui.layout-form',
		'ui.forms',
		'ui.buttons',
		'ui.common',
		'ui.alerts',
		'catalog.product-selector',
		'ui.entity-selector',
		'ui.vue.vuex',
		'ui.vue',
		'main.popup',
		'main.core',
		'main.loader',
		'ui.label',
		'ui.messagecard',
		'ui.vue.components.hint',
		'ui.notification',
		'ui.info-helper',
		'main.qrcode',
		'clipboard',
		'helper',
		'main.core.events',
		'currency.currency-core',
		'catalog.product-calculator',
	],
	'settings' => [
		'showDiscountBlock' => \CUserOptions::GetOption('catalog.product-form', 'showDiscountBlock', 'Y'),
		'showTaxBlock' => 'N',
		'taxIncluded' => 'N',
		'currency' => $currencyId,
		'currencySymbol' => $currencySymbol,
		'isEnabledLanding' => $isEnabledLanding,
		'hasLandingStore' => $hasLandingStore,
		'isLimitedLandingStore' => $isLimitedLanding,
		'basePriceId' => $basePriceId,
		'hiddenCompilationInfoMessage' => \CUserOptions::GetOption('catalog.product-form', 'hiddenCompilationInfoMessage') === 'Y',
	],
	'skip_core' => false,
];