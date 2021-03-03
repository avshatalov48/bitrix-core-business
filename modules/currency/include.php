<?
require_once __DIR__.'/autoload.php';

//class_alias('Bitrix\Currency\UserField\Types\Money', 'Bitrix\Currency\UserField\Money');

\CJSCore::RegisterExt(
	'currency',
	array(
		'js' => '/bitrix/js/currency/core_currency.js',
		'rel' => array('core', 'main.polyfill.promise', 'currency.currency-core')
	)
);

\CJSCore::RegisterExt(
	'core_money_editor',
	array(
		'rel' => array('core', 'currency.money-editor'),
		'oninit' => function()
		{
			return array(
				'lang_additional' => array(
					'CURRENCY' => \Bitrix\Currency\Helpers\Editor::getListCurrency(),
				),
			);
		}
	)
);

\CJSCore::RegisterExt(
	'core_uf_money',
	array(
		'js' => '/bitrix/js/currency/core_uf_money.js',
		'css' => '/bitrix/js/currency/css/core_uf_money.css',
		'rel' => array('uf', 'core_money_editor'),
	)
);


define('CURRENCY_CACHE_DEFAULT_TIME', 10800);
define('CURRENCY_ISO_STANDART_URL', 'http://www.iso.org/iso/home/standards/currency_codes.htm');

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