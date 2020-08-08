<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$description = [
	'RETURN' => Loc::getMessage('SALE_HPS_PAYPAL_DESC_RETURN'),
	'RESTRICTION' => Loc::getMessage('SALE_HPS_PAYPAL_DESC_RESTRICTION'),
	'COMMISSION' => Loc::getMessage('SALE_HPS_PAYPAL_DESC_COMMISSION')
];

$data = [
	'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_NAME'),
	'SORT' => 1000,
	'CODES' => [
		'PAYPAL_USER'  => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_USER'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
		],
		'PAYPAL_PWD'  => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_PWD'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
		],
		'PAYPAL_SIGNATURE'  => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_SIGNATURE'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
		],
		'PAYMENT_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_ORDER_ID'),
			'SORT' => 400,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'ID',
				'PROVIDER_KEY' => 'PAYMENT',
			]
		],
		'PAYMENT_DATE_INSERT' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_DATE_INSERT'),
			'SORT' => 500,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'DATE_BILL',
				'PROVIDER_KEY' => 'PAYMENT',
			]
		],
		'PAYMENT_SHOULD_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_SHOULD_PAY'),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'SUM',
				'PROVIDER_KEY' => 'PAYMENT',
			]
		],
		'PAYMENT_CURRENCY' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_CURRENCY'),
			'SORT' => 700,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'CURRENCY',
				'PROVIDER_KEY' => 'PAYMENT',
			]
		],
		'PAYPAL_NOTIFY_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_NOTIFY_URL'),
			'SORT' => 800,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'https://'.$_SERVER['HTTP_HOST'].'/bitrix/tools/sale_ps_result.php',
				'PROVIDER_KEY' => 'VALUE',
			]
		],
		'PS_IS_TEST' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_TEST'),
			'SORT' => 900,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N'
			]
		],
		'PAYPAL_SSL_ENABLE' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_SSL_ENABLE'),
			'SORT' => 1000,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'Y',
				'PROVIDER_KEY' => 'INPUT'
			]
		],
		'PAYPAL_BUTTON_SRC'  => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_BUTTON_SRC'),
			'SORT' => 1100,
			'GROUP' => 'PS_OTHER',
			'DEFAULT' => [
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_PAYPAL_BUTTON_SRC_NAME_VALUE'),
				'PROVIDER_KEY' => 'VALUE',
			]
		],
		'PAYPAL_ON0'  => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_ON0'),
			'GROUP' => 'PS_OTHER',
			'SORT' => 1300,
		],
		'PAYPAL_ON1'  => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_ON1'),
			'GROUP' => 'PS_OTHER',
			'SORT' => 1400
		],
		'PAYPAL_BUSINESS' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_BUSINESS'),
			'SORT' => 1500,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
			'DEFAULT' => [
				'PROVIDER_VALUE' => '',
				'PROVIDER_KEY' => 'VALUE'
			]
		],
		'PAYPAL_IDENTITY_TOKEN' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_IDENTITY_TOKEN'),
			'SORT' => 1600,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL'
		],
		'PAYPAL_RETURN' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_RETURN'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_PAYPAL_RETURN_DESC'),
			'SORT' => 1700,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
		],
		'PAYPAL_LC' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_LS'),
			'SORT' => 1800,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
			'INPUT' => [
				'TYPE' => 'ENUM',
				'OPTIONS' => [
					'RU' => Loc::getMessage('SALE_HPS_PAYPAL_LC_RUSSIAN'),
					'DE' => Loc::getMessage('SALE_HPS_PAYPAL_LC_GERMAN'),
					'US' => Loc::getMessage('SALE_HPS_PAYPAL_LC_ENGLISH')
				],
			]
		],
	]
];
