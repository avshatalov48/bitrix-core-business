<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Config\Option,
	Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$description = [
	'RETURN' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_RETURN'),
	'RESTRICTION' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_RESTRICTION'),
	'COMMISSION' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_COMMISSION'),
	'MAIN' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_DESCRIPTION'),
];

if (IsModuleInstalled('bitrix24'))
{
	$description['REFERRER'] = Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_REFERRER');
}

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$licensePrefix = Loader::includeModule('bitrix24') ? \CBitrix24::getLicensePrefix() : '';
$portalZone = Loader::includeModule('intranet') ? CIntranetUtils::getPortalZone() : '';

if (Loader::includeModule('bitrix24'))
{
	if ($licensePrefix !== 'ru')
	{
		$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
	}
}
elseif (Loader::includeModule('intranet') && $portalZone !== 'ru')
{
	$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

PaySystem\Manager::includeHandler('YandexCheckout');

$data = [
	'NAME' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'YANDEX_CHECKOUT_SHOP_ARTICLE_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_SHOP_ARTICLE_ID'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_SHOP_ARTICLE_ID_DESC'),
			'SORT' => 230,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
		],
		'YANDEX_CHECKOUT_DESCRIPTION' => [
			'NAME' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_PAYMENT_DESCRIPTION'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_PAYMENT_DESCRIPTION_DESC'),
			'SORT' => 250,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_PAYMENT_DESCRIPTION_TEMPLATE'),
			]
		],
		'YANDEX_CHECKOUT_RETURN_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_RETURN_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_RETURN_URL_DESC_2'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_CHANGE_STATUS_PAY'),
			'SORT' => 400,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
			'DEFAULT' => [
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => 'Y',
			]
		],
	]
];

/** @noinspection TypeUnsafeComparisonInspection */
if (Option::get('sale', 'YANDEX_CHECKOUT_OAUTH', false) == false)
{
	$data['CODES']['YANDEX_CHECKOUT_SHOP_ID'] = [
		'NAME' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_SHOP_ID'),
		'DESCRIPTION' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_SHOP_ID_DESC'),
		'SORT' => 100,
		'GROUP' => 'CONNECT_SETTINGS_YANDEX',
	];

	$data['CODES']['YANDEX_CHECKOUT_SECRET_KEY'] = [
		'NAME' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_SECRET_KEY'),
		'DESCRIPTION' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_SECRET_KEY_DESC'),
		'SORT' => 200,
		'GROUP' => 'CONNECT_SETTINGS_YANDEX'
	];

	if (Loader::includeModule('crm'))
	{
		$data['CODES']['YANDEX_CHECKOUT_RECURRING'] = [
			'NAME' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_RECURRING'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_RECURRING_DESC'),
			'SORT' => 350,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
			'HANDLER_MODE' => [
				Sale\Handlers\PaySystem\YandexCheckoutHandler::MODE_BANK_CARD,
				Sale\Handlers\PaySystem\YandexCheckoutHandler::MODE_YANDEX_MONEY,
				Sale\Handlers\PaySystem\YandexCheckoutHandler::MODE_EMBEDDED,
			],
		];
	}
}
