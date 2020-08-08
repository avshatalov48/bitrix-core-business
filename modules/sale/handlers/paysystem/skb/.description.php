<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$description = [
	'MAIN' => Loc::getMessage('SALE_HPS_SKB_DESCRIPTION_MAIN'),
];

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
	'NAME' => Loc::getMessage('SALE_HPS_SKB'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'SKB_LOGIN' => [
			'NAME' => Loc::getMessage('SALE_HPS_SKB_LOGIN'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_SKB_LOGIN_DESC'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_SKB',
		],
		'SKB_PASSWORD' => [
			'NAME' => Loc::getMessage('SALE_HPS_SKB_PASSWORD'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_SKB_PASSWORD_DESC'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_SKB'
		],
		'SKB_MERCHANT_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_SKB_MERCHANT_ID'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_SKB'
		],
		'SKB_SECRET_KEY' => [
			'NAME' => Loc::getMessage('SALE_HPS_SKB_SECRET_KEY'),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_SKB'
		],
		'SKB_ADDITIONAL_INFO' => [
			'NAME' => Loc::getMessage('SALE_HPS_SKB_ADDITIONAL_INFO'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_SKB_ADDITIONAL_INFO_DESC'),
			'SORT' => 500,
			'GROUP' => 'CONNECT_SETTINGS_SKB',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_SKB_ADDITIONAL_INFO_TEMPLATE'),
			]
		],
		'SKB_TEST_MODE' => [
			'NAME' => Loc::getMessage('SALE_HPS_SKB_TEST_MODE'),
			'SORT' => 600,
			'GROUP' => 'CONNECT_SETTINGS_SKB',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_SKB_CHANGE_STATUS_PAY'),
			'SORT' => 700,
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