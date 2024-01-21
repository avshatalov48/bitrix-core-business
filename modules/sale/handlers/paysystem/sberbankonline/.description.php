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
	'NAME' => Loc::getMessage('SALE_HPS_SBERBANK'),
	'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBERBANK_DESCRIPTION'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'SBERBANK_LOGIN' => [
			'NAME' => Loc::getMessage('SALE_HPS_SBERBANK_LOGIN'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBERBANK_LOGIN_DESC'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK',
		],
		'SBERBANK_PASSWORD' => [
			'NAME' => Loc::getMessage('SALE_HPS_SBERBANK_PASSWORD'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBERBANK_PASSWORD_DESC'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK'
		],
		'SBERBANK_SECRET_KEY' => [
			'NAME' => Loc::getMessage('SALE_HPS_SBERBANK_SECRET_KEY'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBERBANK_SECRET_KEY_DESC'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK'
		],
		'SBERBANK_RETURN_SUCCESS_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_SBERBANK_RETURN_SUCCESS_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBERBANK_RETURN_SUCCESS_URL_DESC'),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK',
		],
		'SBERBANK_RETURN_FAIL_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_SBERBANK_RETURN_FAIL_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBERBANK_RETURN_FAIL_URL_DESC'),
			'SORT' => 500,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK',
		],
		'SBERBANK_ORDER_DESCRIPTION' => [
			'NAME' => Loc::getMessage('SALE_HPS_SBERBANK_ORDER_DESCRIPTION'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBERBANK_ORDER_DESCRIPTION_DESC'),
			'SORT' => 600,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
					'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_SBERBANK_ORDER_DESCRIPTION_TEMPLATE'),
			]
		],
		'SBERBANK_TEST_MODE' => [
			'NAME' => Loc::getMessage('SALE_HPS_SBERBANK_TEST_MODE'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_SBERBANK_TEST_MODE_DESC'),
			'SORT' => 700,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_SBERBANK_CHANGE_STATUS_PAY'),
			'SORT' => 800,
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
