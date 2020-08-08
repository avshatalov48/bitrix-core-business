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
	if ($licensePrefix !== 'ua')
	{
		$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
	}
}
elseif (Loader::includeModule('intranet') && $portalZone !== 'ua')
{
	$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$data = [
	'NAME' => 'LiqPay',
	'SORT' => 400,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'LIQPAY_MERCHANT_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_LIQPAY_MERCHANT_ID'),
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			'SORT' => 100,
		],
		'LIQPAY_SIGN' => [
			'NAME' => Loc::getMessage('SALE_HPS_LIQPAY_SIGN'),
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			'SORT' => 200,
		],
		'LIQPAY_PATH_TO_RESULT_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_LIQPAY_PATH_TO_RESULT_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_LIQPAY_PATH_TO_RESULT_URL_DESC'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
		],
		'LIQPAY_PATH_TO_SERVER_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_LIQPAY_PATH_TO_SERVER_URL'),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'https://'.$_SERVER['HTTP_HOST'].'/bitrix/tools/sale_ps_result.php',
				'PROVIDER_KEY' => 'VALUE'
			]
		],
		'PAYMENT_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_LIQPAY_ORDER_ID'),
			'SORT' => 500,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'ID',
				'PROVIDER_KEY' => 'PAYMENT'
			]
		],
		'PAYMENT_CURRENCY' => [
			'NAME' => Loc::getMessage('SALE_HPS_LIQPAY_CURRENCY'),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'CURRENCY',
				'PROVIDER_KEY' => 'PAYMENT'
			]
		],
		'PAYMENT_SHOULD_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_LIQPAY_SHOULD_PAY'),
			'SORT' => 700,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'SUM',
				'PROVIDER_KEY' => 'PAYMENT'
			]
		],
		'BUYER_PERSON_PHONE' => [
			'NAME' => Loc::getMessage('SALE_HPS_LIQPAY_PHONE'),
			'SORT' => 800,
			'GROUP' => 'BUYER_PERSON',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'PHONE',
				'PROVIDER_KEY' => 'PROPERTY'
			]
		],
		'LIQPAY_PAY_METHOD' => [
			'NAME' => Loc::getMessage('SALE_HPS_LIQPAY_PAYMENT_PM'),
			'SORT' => 900,
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY'
		],
		'LIQPAY_PAYMENT_DESCRIPTION' => [
			'NAME' => Loc::getMessage('SALE_HPS_LIQPAY_PAYMENT_DESCRIPTION'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_LIQPAY_PAYMENT_DESCRIPTION_DESC'),
			'SORT' => 1000,
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_LIQPAY_PAYMENT_DESCRIPTION_TEMPLATE'),
			]
		],
	]
];

