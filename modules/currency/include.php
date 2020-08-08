<?
use Bitrix\Main\Loader;

global $DB;
$strDBType = mb_strtolower($DB->type);

Loader::registerAutoLoadClasses(
	'currency',
	array(
		'CCurrency' => 'general/currency.php',
		'CCurrencyLang' => 'general/currency_lang.php',
		'CCurrencyRates' => $strDBType.'/currency_rate.php',
		'\Bitrix\Currency\Compatible\Tools' => 'lib/compatible/tools.php',
		'\Bitrix\Currency\Helpers\Admin\Tools' => 'lib/helpers/admin/tools.php',
		'\Bitrix\Currency\Helpers\Editor' => 'lib/helpers/editor.php',
		'\Bitrix\Currency\UserField\Money' => 'lib/userfield/money.php',
		'\Bitrix\Currency\CurrencyManager' => 'lib/currencymanager.php',
		'\Bitrix\Currency\CurrencyTable' => 'lib/currency.php',
		'\Bitrix\Currency\CurrencyLangTable' => 'lib/currencylang.php',
		'\Bitrix\Currency\CurrencyRateTable' => 'lib/currencyrate.php',
		'\Bitrix\Currency\CurrencyClassifier' => 'lib/currencyclassifier.php'
	)
);
unset($strDBType);

//class_alias('Bitrix\Currency\UserField\Types\Money', 'Bitrix\Currency\UserField\Money');

\CJSCore::RegisterExt(
	'currency',
	array(
		'js' => '/bitrix/js/currency/core_currency.js',
		'rel' => array('core', 'main.polyfill.promise')
	)
);

\CJSCore::RegisterExt(
	'core_money_editor',
	array(
		'js' => '/bitrix/js/currency/core_money_editor.js',
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