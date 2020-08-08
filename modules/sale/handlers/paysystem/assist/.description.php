<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

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

$data = [
	'NAME' => Loc::getMessage('SALE_HPS_ASSIST'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'ASSIST_SHOP_IDP' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_SHOP_IDP'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		],
		'ASSIST_SHOP_LOGIN' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_SHOP_LOGIN'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		],
		'ASSIST_SHOP_PASSWORD' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_SHOP_PASSWORD'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		],
		'ASSIST_SERVER_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_SERVER_URL'),
			'SORT' => 350,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'payments.paysecure.ru',
				'PROVIDER_KEY' => 'VALUE'
			]
		],
		'ASSIST_SHOP_SECRET_WORLD' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_SHOP_SECRET_WORLD'),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		],
		'ASSIST_DELAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_ASSIST_DELAY'),
			'SORT' => 500,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		],
		'PAYMENT_SHOULD_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_SHOULD_PAY'),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			]
		],
		'PAYMENT_CURRENCY' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_CURRENCY'),
			'SORT' => 700,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'CURRENCY'
			]
		],
		'PAYMENT_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_ORDER_ID'),
			'SORT' => 800,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'ID'
			]
		],
		'PAYMENT_DATE_INSERT' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_DATE_INSERT'),
			'SORT' => 1000,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'DATE_BILL'
			]
		],
		'ASSIST_SUCCESS_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_SUCCESS_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_ASSIST_SUCCESS_URL_DESC'),
			'SORT' => 1100,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST',
		],
		'ASSIST_FAIL_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_FAIL_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_ASSIST_FAIL_URL_DESC'),
			'SORT' => 1200,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST',
		],
		'BUYER_PERSON_NAME_FIRST' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_FIRST_NAME'),
			'SORT' => 1300,
			'GROUP' => 'BUYER_PERSON'
		],
		'BUYER_PERSON_NAME_SECOND' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_MIDDLE_NAME'),
			'SORT' => 1400,
			'GROUP' => 'BUYER_PERSON'
		],
		'BUYER_PERSON_NAME_LAST' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_LAST_NAME'),
			'SORT' => 1500,
			'GROUP' => 'BUYER_PERSON'
		],
		'BUYER_PERSON_EMAIL' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_EMAIL'),
			'SORT' => 1600,
			'GROUP' => 'BUYER_PERSON'
		],
		'BUYER_PERSON_ADDRESS' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_ADDRESS'),
			'SORT' => 1700,
			'GROUP' => 'BUYER_PERSON'
		],
		'BUYER_PERSON_PHONE' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_PHONE'),
			'SORT' => 1800,
			'GROUP' => 'BUYER_PERSON'
		],
		'ASSIST_PAYMENT_CardPayment' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_PAYMENT_CardPayment'),
			'SORT' => 1900,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		],
		'ASSIST_PAYMENT_YMPayment' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_PAYMENT_YMPayment'),
			'SORT' => 2000,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		],
		'ASSIST_PAYMENT_WebMoneyPayment' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_PAYMENT_WebMoneyPayment'),
			'SORT' => 2100,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		],
		'ASSIST_PAYMENT_QIWIPayment' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_PAYMENT_QIWIPayment'),
			'SORT' => 2200,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		],
		'ASSIST_PAYMENT_AssistIDCCPayment' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_PAYMENT_AssistIDCCPayment'),
			'SORT' => 2300,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_AUTOPAY'),
			'SORT' => 2400,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
			'DEFAULT' => [
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => 'Y',
			]
		],
		'PS_IS_TEST' => [
			'NAME' => Loc::getMessage('SALE_HPS_ASSIST_DEMO'),
			'SORT' => 2500,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N'
			]
		]
	]
];