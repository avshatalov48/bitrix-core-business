<?php
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$isAvailable = Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

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

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$protocol = $request->isHttps() ? 'https' : 'http';

$data = [
	'NAME' => Loc::getMessage('SALE_HPS_PLATON'),
	'HANDLER_MODE_LIST' => [
		'bank_card' => Loc::getMessage('SALE_HPS_PLATON_MODE_CARD'),
		'google_pay' => Loc::getMessage('SALE_HPS_PLATON_MODE_GOOGLE_PAY'),
		'apple_pay' => Loc::getMessage('SALE_HPS_PLATON_MODE_APPLE_PAY'),
		'privat24' => Loc::getMessage('SALE_HPS_PLATON_MODE_PRIVAT24'),
	],
	'HANDLER_MODE_DESCRIPTION_LIST' => [
		'bank_card' => [
			'MAIN' => Loc::getMessage('SALE_HPS_PLATON_DESCRIPTION'),
			'PUBLIC' => '',
		],
		'google_pay' => [
			'MAIN' => Loc::getMessage('SALE_HPS_PLATON_DESCRIPTION'),
			'PUBLIC' => '',
		],
		'apple_pay' => [
			'MAIN' => Loc::getMessage('SALE_HPS_PLATON_DESCRIPTION'),
			'PUBLIC' => '',
		],
		'privat24' => [
			'MAIN' => Loc::getMessage('SALE_HPS_PLATON_DESCRIPTION'),
			'PUBLIC' => '',
		],
	],
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'PLATON_API_KEY' => [
			'NAME' => Loc::getMessage('SALE_HPS_PLATON_API_KEY'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_PLATON_API_KEY_DESCRIPTION'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_PLATON',
		],
		'PLATON_PASSWORD' => [
			'NAME' => Loc::getMessage('SALE_HPS_PLATON_PASSWORD'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_PLATON',
		],
		'PLATON_PAYMENT_DESCRIPTION' => [
			'NAME' => Loc::getMessage('SALE_HPS_PLATON_PAYMENT_DESCRIPTION'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_PLATON_PAYMENT_DESCRIPTION_DESCRIPTION'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_PLATON',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_PLATON_INVOICE_DESCRIPTION_DEFAULT_TEMPLATE'),
			]
		],
		'PLATON_SUCCESS_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_PLATON_SUCCESS_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_PLATON_SUCCESS_URL_DESCRIPTION'),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_PLATON',
		],
	],
];
