<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$protocol = $request->isHttps() ? 'https://' : 'http://';

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;
if (Loader::includeModule('bitrix24'))
{
	if (\CBitrix24::getLicensePrefix() !== 'by')
	{
		$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
	}
}
elseif (Loader::includeModule('intranet') && CIntranetUtils::getPortalZone() !== 'ru')
{
	$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}
$description = [
	'MAIN' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_DESCRIPTION_MAIN'),
];
$data = [
	'NAME' => Loc::getMessage('SALE_HPS_BEPAID_ERIP'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'BEPAID_ERIP_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_SHOP_ID'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
		],
		'BEPAID_ERIP_SECRET_KEY' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_SECRET_KEY'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
		],
		'BEPAID_ERIP_PUBLIC_KEY' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_PUBLIC_KEY'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
		],
		'BEPAID_ERIP_SERVICE_CODE' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_SERVICE_CODE'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_SERVICE_CODE_DESC'),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
		],
		'BEPAID_ERIP_PAYMENT_DESCRIPTION' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_PAYMENT_DESCRIPTION'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_PAYMENT_DESCRIPTION_DESC'),
			'SORT' => 500,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_PAYMENT_DESCRIPTION_TEMPLATE'),
			],
		],
		'BEPAID_ERIP_NOTIFICATION_URL' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_NOTIFICATION_URL'),
			'SORT' => 600,
			'GROUP' => 'CONNECT_SETTINGS_BEPAID',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => $protocol . $request->getHttpHost() . '/bitrix/tools/sale_ps_result.php',
			],
		],
		'PS_IS_TEST' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_IS_TEST'),
			'SORT' => 700,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_CHANGE_STATUS_PAY'),
			'SORT' => 800,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N',
			],
			'DEFAULT' => [
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => 'Y',
			]
		]
	]
];
