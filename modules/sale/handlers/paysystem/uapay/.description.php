<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$host = $request->isHttps() ? 'https' : 'http';

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
	'NAME' => Loc::getMessage('SALE_HPS_UAPAY'),
	'PUBLIC_DESCRIPTION' => Loc::getMessage('SALE_HPS_UAPAY_PUBLIC_DESCRIPTION'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'UAPAY_CLIENT_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_UAPAY_CLIENT_ID'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_UAPAY',
		],
		'UAPAY_SIGN_KEY' => [
			'NAME' => Loc::getMessage('SALE_HPS_UAPAY_SIGN_KEY'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_UAPAY_SIGN_KEY_DESC'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_UAPAY',
		],
		'UAPAY_CALLBACK_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_UAPAY_CALLBACK_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_UAPAY_CALLBACK_URL_DESC'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_UAPAY',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => $host.'://'.$request->getHttpHost().'/bitrix/tools/sale_ps_result.php',
			]
		],
		'UAPAY_REDIRECT_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_UAPAY_REDIRECT_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_UAPAY_REDIRECT_URL_DESC'),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_UAPAY',
		],
		'UAPAY_INVOICE_DESCRIPTION' => [
			'NAME' => Loc::getMessage('SALE_HPS_UAPAY_INVOICE_DESCRIPTION'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_UAPAY_INVOICE_DESCRIPTION_DESC'),
			'SORT' => 500,
			'GROUP' => 'CONNECT_SETTINGS_UAPAY',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_UAPAY_INVOICE_DESCRIPTION_TEMPLATE'),
			]
		],
		'UAPAY_TEST_MODE' => [
			'NAME' => Loc::getMessage('SALE_HPS_UAPAY_TEST_MODE'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_UAPAY_TEST_MODE_DESC'),
			'SORT' => 600,
			'GROUP' => 'CONNECT_SETTINGS_UAPAY',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_UAPAY_CHANGE_STATUS_PAY'),
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
