<?php

/** @global CMain $APPLICATION */
use Bitrix\Main\Localization\Loc;

if ($APPLICATION->GetGroupRight('currency') <= 'D')
{
	return false;
}

return [
	'parent_menu' => 'global_menu_settings',
	'section' => 'currency',
	'sort' => 300,
	'text' => Loc::getMessage('CURRENCY_CONTROL'),
	'title' => Loc::getMessage('currency_menu_title'),
	'icon' => 'currency_menu_icon',
	'page_icon' => 'currency_page_icon',
	'items_id' => 'menu_currency',
	'items' => [
		[
			'text' => Loc::getMessage('CURRENCY'),
			'title' => Loc::getMessage('CURRENCY_ALT'),
			'url' => 'currencies.php?lang=' . LANGUAGE_ID,
			'more_url' => [
				'currency_edit.php',
				'currency_add_from_classifier.php',
			],
		],
		[
			'text' => Loc::getMessage('CURRENCY_RATES'),
			'title' => Loc::getMessage('CURRENCY_RATES_ALT'),
			'url' => 'currencies_rates.php?lang=' . LANGUAGE_ID,
			'more_url' => [
				'currency_rate_edit.php',
			],
		],
	],
];
