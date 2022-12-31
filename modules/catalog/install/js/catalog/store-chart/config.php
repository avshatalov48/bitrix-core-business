<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$currencyId = '';
$currencyFormat = '';
$currencySymbol = '';
if (\Bitrix\Main\Loader::includeModule('currency'))
{
	$currencyId = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
	$currencyFormat = \CCurrencyLang::GetFormatDescription($currencyId);
	$currencySymbol = isset($currencyFormat['FORMAT_STRING']) ? trim(\CCurrencyLang::applyTemplate('', $currencyFormat['FORMAT_STRING'])) : '';
}

return [
	'js' => 'dist/store-chart.bundle.js',
	'css' => 'dist/store-chart.bundle.css',
	'rel' => [
		'main.popup',
		'currency.currency-core',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'currency' => $currencyId,
		'currencySymbol' => $currencySymbol,
		'currencyFormat' => $currencyFormat,
	]
];
