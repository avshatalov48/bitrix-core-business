<?php

require_once __DIR__ . '/autoload.php';

\CJSCore::RegisterExt(
	'currency',
	[
		'js' => '/bitrix/js/currency/core_currency.js',
		'rel' => [
			'core',
			'main.polyfill.promise',
			'currency.currency-core',
		],
	]
);

\CJSCore::RegisterExt(
	'core_money_editor',
	[
		'rel' => [
			'core',
			'currency.money-editor',
		],
		'oninit' => function()
		{
			return [
				'lang_additional' => [
					'CURRENCY' => \Bitrix\Currency\Helpers\Editor::getListCurrency(),
				],
			];
		},
	]
);

\CJSCore::RegisterExt(
	'core_uf_money',
	[
		'js' => '/bitrix/js/currency/core_uf_money.js',
		'css' => '/bitrix/js/currency/css/core_uf_money.css',
		'rel' => [
			'uf',
			'core_money_editor',
		],
	]
);

const CURRENCY_CACHE_DEFAULT_TIME = 10800;
const CURRENCY_ISO_STANDART_URL = 'https://www.iso.org/iso/home/standards/currency_codes.htm';

/*
* @deprecated deprecated since currency 14.0.0
* @see CCurrencyLang::CurrencyFormat()
*/
function CurrencyFormat($price, $currency)
{
	return CCurrencyLang::CurrencyFormat($price, $currency, true);
}

/*
* @deprecated deprecated since currency 14.0.0
* @see CCurrencyLang::CurrencyFormat()
*/
function CurrencyFormatNumber($price, $currency)
{
	return CCurrencyLang::CurrencyFormat($price, $currency, false);
}
