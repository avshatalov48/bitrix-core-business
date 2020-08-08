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

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$protocol = $request->isHttps() ? 'https' : 'http';

$data = [
	'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'WEBMONEY_SHOP_ACCT' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_NUMBER'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_WEBMONEY',
		],
		'PS_IS_TEST' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_TEST'),
			'SORT' => 200,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N'
			]
		],
		'WEBMONEY_CNST_SECRET_KEY' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_KEY'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_WEBMONEY'
		],
		'WEBMONEY_HASH_ALGO' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_HASH_ALGO'),
			'SORT' => 400,
			'TYPE' => 'SELECT',
			'GROUP' => 'CONNECT_SETTINGS_WEBMONEY',
			'INPUT' => [
				'TYPE' => 'ENUM',
				'OPTIONS' => [
					'md5' => 'md5',
					'sha256' => 'sha256'
				]
			]
		],
		'PAYMENT_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_PAYMENT_ID'),
			'SORT' => 500,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'ID'
			]
		],
		'PAYMENT_DATE_INSERT' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_DATE'),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'DATE_BILL'
			]
		],
		'PAYMENT_SHOULD_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_SUMMA'),
			'SORT' => 700,
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			],
			'GROUP' => 'PAYMENT'
		],
		'WEBMONEY_RESULT_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_URL'),
			'SORT' => 800,
			'GROUP' => 'CONNECT_SETTINGS_WEBMONEY',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => $protocol.'://'.$request->getHttpHost().'/bitrix/tools/sale_ps_result.php',
			]
		],
		'WEBMONEY_SUCCESS_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_URL_OK'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_WEBMONEY_URL_OK_DESC'),
			'SORT' => 900,
			'GROUP' => 'CONNECT_SETTINGS_WEBMONEY',
		],
		'WEBMONEY_FAIL_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_URL_ERROR'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_WEBMONEY_URL_ERROR_DESC'),
			'SORT' => 1000,
			'GROUP' => 'CONNECT_SETTINGS_WEBMONEY',
		],
		'BUYER_PERSON_PHONE' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_PHONE'),
			'GROUP' => 'BUYER_PERSON',
			'SORT' => 1100,
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PROPERTY',
				'PROVIDER_VALUE' => 'PHONE'
			]
		],
		'BUYER_PERSON_EMAIL' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_MAIL'),
			'GROUP' => 'BUYER_PERSON',
			'SORT' => 1200,
			'DEFAULT' => [
				'PROVIDER_KEY' => 'PROPERTY',
				'PROVIDER_VALUE' => 'EMAIL'
			]
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY_CHANGE_STATUS_PAY'),
			'SORT' => 1300,
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
