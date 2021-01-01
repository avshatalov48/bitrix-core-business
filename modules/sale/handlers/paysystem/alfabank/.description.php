<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$description = array(
	'MAIN' => Loc::getMessage('SALE_HPS_ALFABANK_DESCRIPTION_MAIN'),
);

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$portalZone = Loader::includeModule('intranet') ? CIntranetUtils::getPortalZone() : '';
$licensePrefix = Loader::includeModule('bitrix24') ? \CBitrix24::getLicensePrefix() : '';

if (Loader::includeModule("bitrix24"))
{
	if ($licensePrefix !== 'by')
	{
		$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
	}
}
elseif (Loader::includeModule('intranet') && $portalZone !== 'ru')
{
	$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$data = [
	'NAME' => Loc::getMessage('SALE_HPS_ALFABANK'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'ALFABANK_LOGIN' => [
			'NAME' => Loc::getMessage('SALE_HPS_ALFABANK_LOGIN'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_ALFABANK',
		],
		'ALFABANK_PASSWORD' => [
			'NAME' => Loc::getMessage('SALE_HPS_ALFABANK_PASSWORD'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_ALFABANK'
		],
		'ALFABANK_MERCHANT' => [
			'NAME' => Loc::getMessage('SALE_HPS_ALFABANK_MERCHANT'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_ALFABANK'
		],
		'ALFABANK_SECRET_KEY' => [
			'NAME' => Loc::getMessage('SALE_HPS_ALFABANK_SECRET_KEY'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_ALFABANK_SECRET_KEY_DESC'),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_ALFABANK'
		],
		'ALFABANK_RETURN_SUCCESS_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_ALFABANK_RETURN_SUCCESS_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_ALFABANK_RETURN_SUCCESS_URL_DESC'),
			'SORT' => 500,
			'GROUP' => 'CONNECT_SETTINGS_ALFABANK',
		],
		'ALFABANK_RETURN_FAIL_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_ALFABANK_RETURN_FAIL_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_ALFABANK_RETURN_FAIL_URL_DESC'),
			'SORT' => 600,
			'GROUP' => 'CONNECT_SETTINGS_ALFABANK',
		],
		'ALFABANK_ORDER_DESCRIPTION' => [
			'NAME' => Loc::getMessage('SALE_HPS_ALFABANK_ORDER_DESCRIPTION'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_ALFABANK_ORDER_DESCRIPTION_DESC'),
			'SORT' => 700,
			'GROUP' => 'CONNECT_SETTINGS_ALFABANK',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_ALFABANK_ORDER_DESCRIPTION_TEMPLATE'),
			]
		],
		'ALFABANK_TEST_MODE' => [
			'NAME' => Loc::getMessage('SALE_HPS_ALFABANK_TEST_MODE'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_ALFABANK_TEST_MODE_DESC'),
			'SORT' => 800,
			'GROUP' => 'CONNECT_SETTINGS_ALFABANK',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_ALFABANK_CHANGE_STATUS_PAY'),
			'SORT' => 900,
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