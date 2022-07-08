<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
	'currency',
	[
		'CCurrency' => 'general/currency.php',
		'CCurrencyLang' => 'general/currency_lang.php',
		'CCurrencyRates' => 'mysql/currency_rate.php',
		'\Bitrix\Currency\Compatible\Tools' => 'lib/compatible/tools.php',
		'\Bitrix\Currency\Helpers\Admin\Tools' => 'lib/helpers/admin/tools.php',
		'\Bitrix\Currency\Helpers\Editor' => 'lib/helpers/editor.php',
		'\Bitrix\Currency\UserField\Money' => 'lib/userfield/money.php',
		'\Bitrix\Currency\CurrencyManager' => 'lib/currencymanager.php',
		'\Bitrix\Currency\CurrencyTable' => 'lib/currency.php',
		'\Bitrix\Currency\CurrencyLangTable' => 'lib/currencylang.php',
		'\Bitrix\Currency\CurrencyRateTable' => 'lib/currencyrate.php',
		'\Bitrix\Currency\CurrencyClassifier' => 'lib/currencyclassifier.php',
	]
);
