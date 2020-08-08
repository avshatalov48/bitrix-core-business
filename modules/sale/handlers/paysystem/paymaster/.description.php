<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Application,
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

$request = Application::getInstance()->getContext()->getRequest();
$protocol = $request->isHttps() ? 'https://' : 'http://';

$data = [
	'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'PAYMASTER_SHOP_ACCT' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_NUMBER'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_PAYMASTER',
		],
		'PS_IS_TEST' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_TEST'),
			'SORT' => 200,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N'
			]
		],
		'PAYMASTER_CNST_SECRET_KEY' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_KEY'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_PAYMASTER'
		],
		'PAYMENT_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_ORDER_ID'),
			'SORT' => 500,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'ID'
			]
		],
		'PAYMENT_DATE_INSERT' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_DATE'),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'DATE_BILL'
			]
		],
		'PAYMENT_SHOULD_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_SUMMA'),
			'SORT' => 700,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			]
		],
		'PAYMENT_CURRENCY' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_CURRENCY'),
			'SORT' => 800,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'CURRENCY'
			]
		],
		'PAYMASTER_RESULT_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_URL'),
			'SORT' => 900,
			'GROUP' => 'CONNECT_SETTINGS_PAYMASTER',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => $protocol.$request->getHttpHost().'/bitrix/tools/sale_ps_result.php',
			],
		],
		'PAYMASTER_SUCCESS_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_URL_OK'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_PAYMASTER_URL_OK_DESC'),
			'SORT' => 1000,
			'GROUP' => 'CONNECT_SETTINGS_PAYMASTER'
		],
		'PAYMASTER_FAIL_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_URL_ERROR'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_PAYMASTER_URL_ERROR_DESC'),
			'SORT' => 1100,
			'GROUP' => 'CONNECT_SETTINGS_PAYMASTER'
		],
		'BUYER_PERSON_PHONE' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_PHONE'),
			'GROUP' => 'BUYER_PERSON',
			'SORT' => 1200
		],
		'PAYMASTER_HASH_ALGO' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_HASH_ALGO'),
			'SORT' => 1250,
			'TYPE' => 'SELECT',
			'GROUP' => 'CONNECT_SETTINGS_PAYMASTER',
			'INPUT' => [
				'TYPE' => 'ENUM',
				'OPTIONS' => [
					'md5' => 'md5',
					'sha256' => 'sha256'
				]
			],
			'DEFAULT' => [
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => 'md5'
			]
		],
		'BUYER_PERSON_EMAIL' => [
			'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER_MAIL'),
			'GROUP' => 'BUYER_PERSON',
			'SORT' => 1300
		]
	]
];