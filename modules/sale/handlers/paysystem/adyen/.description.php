<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

PaySystem\Manager::includeHandler('Adyen');

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$portalZone = Loader::includeModule('intranet') ? CIntranetUtils::getPortalZone() : "";
$licensePrefix = Loader::includeModule('bitrix24') ? \CBitrix24::getLicensePrefix() : "";

if (in_array($portalZone, ["ua", "ru", "by", "kz"]) || in_array($licensePrefix, ["ua", "ru", "by", "kz"]))
{
	$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$data = [
	'NAME' => Loc::getMessage('SALE_HPS_ADYEN_PROVIDER_NAME'),
	'SORT' => 100,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'ADYEN_MERCHANT_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_ADYEN_MERCHANT_ID'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_ADYEN_MERCHANT_ID_DESC'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_ADYEN',
		],
		'ADYEN_X_API_KEY' => [
			'NAME' => Loc::getMessage('SALE_HPS_ADYEN_X_API_KEY'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_ADYEN_X_API_KEY_DESC'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_ADYEN',
		],
		'ADYEN_HMAC_KEY' => [
			'NAME' => Loc::getMessage('SALE_HPS_ADYEN_HMAC_KEY'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_ADYEN_HMAC_KEY_DESC'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_ADYEN',
		],
		'ADYEN_LIVE_URL_PREFIX' => [
			'NAME' => Loc::getMessage('SALE_HPS_ADYEN_LIVE_URL_PREFIX'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_ADYEN_LIVE_URL_PREFIX_DESC'),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_ADYEN',
		],
		'PS_IS_TEST' => [
			'NAME' => Loc::getMessage('SALE_HPS_ADYEN_IS_TEST'),
			'SORT' => 500,
			'GROUP' => 'CONNECT_SETTINGS_ADYEN',
			'INPUT' => [
				'TYPE' => 'Y/N'
			]
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_ADYEN_CHANGE_STATUS_PAY'),
			'SORT' => 600,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
		],
		'APPLE_PAY_MERCHANT_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_APPLE_PAY_MERCHANT_ID'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_APPLE_PAY_MERCHANT_ID_DESC'),
			'SORT' => 700,
			'GROUP' => 'CONNECT_SETTINGS_APPLE_PAY',
			'HANDLER_MODE' => [
				Sale\Handlers\PaySystem\AdyenHandler::PAYMENT_METHOD_APPLE_PAY
			],
		],
		'APPLE_PAY_MERCHANT_DISPLAY_NAME' => [
			'NAME' => Loc::getMessage('SALE_HPS_APPLE_PAY_MERCHANT_DISPLAY_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_APPLE_PAY_MERCHANT_DISPLAY_NAME_DESC'),
			'SORT' => 800,
			'GROUP' => 'CONNECT_SETTINGS_APPLE_PAY',
			'HANDLER_MODE' => [
				Sale\Handlers\PaySystem\AdyenHandler::PAYMENT_METHOD_APPLE_PAY
			],
		],
		'APPLE_PAY_CERT_FILE' => [
			'NAME' => Loc::getMessage('SALE_HPS_APPLE_PAY_CERT_FILE'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_APPLE_PAY_CERT_FILE_DESC'),
			'SORT' => 900,
			'GROUP' => 'CONNECT_SETTINGS_APPLE_PAY',
			'INPUT' => [
				'TYPE' => 'DATABASE_FILE'
			],
			'HANDLER_MODE' => [
				Sale\Handlers\PaySystem\AdyenHandler::PAYMENT_METHOD_APPLE_PAY
			],
		],
		'APPLE_PAY_DOMAIN' => [
			'NAME' => Loc::getMessage('SALE_HPS_APPLE_PAY_DOMAIN'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_APPLE_PAY_DOMAIN_DESC'),
			'SORT' => 1000,
			'GROUP' => 'CONNECT_SETTINGS_APPLE_PAY',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => $request->getHttpHost()
			],
			'HANDLER_MODE' => [
				Sale\Handlers\PaySystem\AdyenHandler::PAYMENT_METHOD_APPLE_PAY
			],
		],
		'APPLE_PAY_COUNTRY_CODE' => [
			'NAME' => Loc::getMessage('SALE_HPS_APPLE_PAY_COUNTRY_CODE'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_APPLE_PAY_COUNTRY_CODE_DESC'),
			'SORT' => 1200,
			'GROUP' => 'CONNECT_SETTINGS_APPLE_PAY',
			'HANDLER_MODE' => [
				Sale\Handlers\PaySystem\AdyenHandler::PAYMENT_METHOD_APPLE_PAY
			],
		],
	],
];


