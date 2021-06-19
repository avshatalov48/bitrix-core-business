<?
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
if (\Bitrix\Main\Loader::includeModule('catalog'))
{
    $baseGroup = \CCatalogGroup::GetBaseGroup();
    $basePriceId = (is_array($baseGroup) && isset($baseGroup['ID'])) ? (int)$baseGroup['ID'] : null;
}

return [
	'css' => 'dist/product-form.bundle.css',
	'js' => 'dist/product-form.bundle.js',
	'rel' => [
		'ui.notification',
		'currency',
		'ui.layout-form',
		'ui.forms',
		'ui.buttons',
		'catalog.product-selector',
		'ui.common',
		'ui.alerts',
		'ui.vue.vuex',
		'main.popup',
		'main.core',
		'ui.vue',
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
		'basePriceId' => $basePriceId,
	],
	'skip_core' => false,
];