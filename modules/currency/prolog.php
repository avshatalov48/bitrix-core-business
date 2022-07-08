<?php
use Bitrix\Main\Localization\Loc;

const ADMIN_MODULE_NAME = 'currency';

define(
	'ADMIN_MODULE_ICON',
	'<a href="/bitrix/admin/currencies_rates.php?lang=' . LANGUAGE_ID
		. '"><img src="/bitrix/images/currency/currency.gif" width="48" height="48" border="0" alt="'
		. Loc::getMessage('CURRENCY_ICON_TITLE') . '" title="'
		. Loc::getMessage('CURRENCY_ICON_TITLE') . '"></a>'
);
