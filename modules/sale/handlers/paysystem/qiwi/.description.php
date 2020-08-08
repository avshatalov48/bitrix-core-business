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
	'NAME' => Loc::getMessage('SALE_HPS_QIWI_NAME'),
	'SORT' => 750,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'QIWI_SHOP_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_SHOP_ID'),
			'GROUP' => 'CONNECT_SETTINGS_QIWI',
			'SORT' => 100,

		],
		'QIWI_API_LOGIN' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_API_LOGIN'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',

		],
		'QIWI_API_PASSWORD' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_API_PASS'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',

		],
		'QIWI_NOTICE_PASSWORD' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_NOTICE_PASSWORD'),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',

		],
		'BUYER_PERSON_PHONE' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_CLIENT_PHONE'),
			'SORT' => 500,
			'GROUP' => 'BUYER_PERSON',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'PHONE',
				'PROVIDER_KEY' => 'PROPERTY'
			]
		],
		'PAYMENT_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_ORDER_ID'),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'ID',
				'PROVIDER_KEY' => 'PAYMENT'
			]
		],
		'PAYMENT_SHOULD_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_SHOULD_PAY'),
			'SORT' => 700,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'SUM',
				'PROVIDER_KEY' => 'PAYMENT'
			]
		],
		'PAYMENT_CURRENCY' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_CURRENCY'),
			'SORT' => 800,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'CURRENCY',
				'PROVIDER_KEY' => 'PAYMENT'
			]
		],
		'QIWI_BILL_LIFETIME' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_BILL_LIFETIME'),
			'SORT' => 900,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',
			'DEFAULT' => [
				'PROVIDER_VALUE' => '240',
				'PROVIDER_KEY' => 'VALUE'
			]
		],
		'QIWI_AUTHORIZATION' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_AUTHORIZATION'),
			'SORT' => 1000,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => 'OPEN',
			]
		],
		'QIWI_SUCCESS_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_SUCCESS_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_QIWI_SUCCESS_URL_DESC'),
			'SORT' => 1100,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',
		],
		'QIWI_FAIL_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_FAIL_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_QIWI_FAIL_URL_DESC'),
			'SORT' => 1200,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_QIWI_CHANGE_STATUS_PAY'),
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
			'DEFAULT' => [
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => 'Y',
			]
		]
	]
];
