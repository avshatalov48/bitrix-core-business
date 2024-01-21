<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$description = array(
	'MAIN' => Loc::getMessage('SALE_HPS_WOOPPAY_DESCRIPTION_MAIN'),
);

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$protocol = $request->isHttps() ? 'https://' : 'http://';

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$portalZone = Loader::includeModule('intranet') ? CIntranetUtils::getPortalZone() : '';
$licensePrefix = Loader::includeModule('bitrix24') ? \CBitrix24::getLicensePrefix() : '';

if (Loader::includeModule("bitrix24"))
{
	if ($licensePrefix !== 'kz')
	{
		$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
	}
}
elseif (Loader::includeModule('intranet') && $portalZone !== 'ru')
{
	$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$data = [
	'NAME' => Loc::getMessage('SALE_HPS_WOOPPAY'),
	'HANDLER_MODE_LIST' => [
		'checkout' => Loc::getMessage('SALE_HPS_WOOPPAY_CHECKOUT_MODE'),
	],
	'HANDLER_MODE_DESCRIPTION_LIST' => [
		'checkout' => [
			'MAIN' => Loc::getMessage('SALE_HPS_WOOPPAY_CHECKOUT_MODE_DESCRIPTION'),
			'PUBLIC' => '',
		],
	],
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'WOOPPAY_LOGIN' => [
			'NAME' => Loc::getMessage('SALE_HPS_WOOPPAY_LOGIN'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_WOOPPAY',
		],
		'WOOPPAY_PASSWORD' => [
			'NAME' => Loc::getMessage('SALE_HPS_WOOPPAY_PASSWORD'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_WOOPPAY'
		],
		'SERVICE_NAME' => [
			'NAME' => Loc::getMessage('SALE_HPS_WOOPPAY_SERVICE_NAME'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_WOOPPAY'
		],
		'WOOPPAY_PAYMENT_DESCRIPTION' => [
			'NAME' => Loc::getMessage('SALE_HPS_WOOPPAY_PAYMENT_DESCRIPTION'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_WOOPPAY_PAYMENT_DESCRIPTION_DESC'),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_WOOPPAY',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_WOOPPAY_PAYMENT_DESCRIPTION_TEMPLATE'),
			]
		],
		'WOOPPAY_BACK_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_WOOPPAY_BACK_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_WOOPPAY_BACK_URL_DESC'),
			'SORT' => 500,
			'GROUP' => 'CONNECT_SETTINGS_WOOPPAY',
		],
		'WOOPPAY_REQUEST_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_WOOPPAY_REQUEST_URL'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_WOOPPAY_REQUEST_URL_DESC'),
			'SORT' => 600,
			'GROUP' => 'CONNECT_SETTINGS_WOOPPAY',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => $protocol.$request->getHttpHost().'/bitrix/tools/sale_ps_result.php',
			]
		],
		'WOOPPAY_TEST_MODE' => [
			'NAME' => Loc::getMessage('SALE_HPS_WOOPPAY_TEST_MODE'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_WOOPPAY_TEST_MODE_DESC'),
			'SORT' => 700,
			'GROUP' => 'CONNECT_SETTINGS_WOOPPAY',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_WOOPPAY_CHANGE_STATUS_PAY'),
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
