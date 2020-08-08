<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$protocol = $request->isHttps() ? 'https://' : 'http://';

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

$data = [
	'NAME' => Loc::getMessage('SALE_HPS_BEPAID'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'BEPAID_ID' => [
			'NAME' => 'ID',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BEPAID_ID_DESC'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
		],
		'BEPAID_SECRET_KEY' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_SECRET_KEY'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
		],
		'BEPAID_PAYMENT_DESCRIPTION' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_PAYMENT_DESCRIPTION'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BEPAID_PAYMENT_DESCRIPTION_DESC'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BEPAID_PAYMENT_DESCRIPTION_TEMPLATE'),
			],
		],
		'BEPAID_NOTIFICATION_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_NOTIFICATION_URL'),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => $protocol.$request->getHttpHost().'/bitrix/tools/sale_ps_result.php',
			],
		],
		'BEPAID_SUCCESS_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_SUCCESS_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BEPAID_SUCCESS_URL_DESC'),
			'SORT' => 500,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
		],
		'BEPAID_DECLINE_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_DECLINE_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BEPAID_DECLINE_URL_DESC'),
			'SORT' => 600,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
		],
		'BEPAID_FAIL_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_FAIL_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BEPAID_FAIL_URL_DESC'),
			'SORT' => 700,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
		],
		'BEPAID_CANCEL_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_CANCEL_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BEPAID_CANCEL_URL_DESC'),
			'SORT' => 800,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
		],
		'PS_IS_TEST' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_IS_TEST'),
			'SORT' => 900,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_CHANGE_STATUS_PAY'),
			'SORT' => 1000,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N',
			],
			'DEFAULT' => [
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => 'Y',
			],
		],
	]
];
