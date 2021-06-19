<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$description = [
	'MAIN' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_DESCRIPTION_MAIN'),
];

PaySystem\Manager::includeHandler('Roboxchange');

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$licensePrefix = Loader::includeModule('bitrix24') ? \CBitrix24::getLicensePrefix() : '';
$portalZone = Loader::includeModule('intranet') ? CIntranetUtils::getPortalZone() : '';

if (Loader::includeModule('bitrix24'))
{
	if (!in_array($licensePrefix, ['ru', 'kz'], true))
	{
		$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
	}
}
elseif (Loader::includeModule('intranet') && $portalZone !== 'ru')
{
	$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$data = [
	'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_TITLE'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'ROBOXCHANGE_SHOPLOGIN' => [
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_SHOPLOGIN'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_ROBOXCHANGE',
		],
		'ROBOXCHANGE_SHOPPASSWORD' => [
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_SHOPPASSWORD'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_ROBOXCHANGE',
		],
		'ROBOXCHANGE_SHOPPASSWORD2' => [
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_SHOPPASSWORD2'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_ROBOXCHANGE',
		],
		'ROBOXCHANGE_ORDERDESCR' => [
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_ORDERDESCR'),
			'SORT' => 400,
			'GROUP' => 'PAYMENT',
		],
		'ROBOXCHANGE_SHOPPASSWORD_TEST' => [
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_TEST_SHOPPASSWORD'),
			'SORT' => 500,
			'GROUP' => 'CONNECT_SETTINGS_ROBOXCHANGE',
		],
		'ROBOXCHANGE_SHOPPASSWORD2_TEST' => [
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_TEST_SHOPPASSWORD2'),
			'SORT' => 600,
			'GROUP' => 'CONNECT_SETTINGS_ROBOXCHANGE',
		],
		'ROBOXCHANGE_TEMPLATE_TYPE' => [
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_TEMPLATE_TYPE'),
			'SORT' => 700,
			'GROUP' => 'CONNECT_SETTINGS_ROBOXCHANGE',
			'INPUT' => [
				'TYPE' => 'ENUM',
				'OPTIONS' => [
					Sale\Handlers\PaySystem\RoboxchangeHandler::TEMPLATE_TYPE_CHECKOUT => Loc::getMessage('SALE_HPS_ROBOXCHANGE_TEMPLATE_TYPE_CHECKOUT'),
					Sale\Handlers\PaySystem\RoboxchangeHandler::TEMPLATE_TYPE_IFRAME => Loc::getMessage('SALE_HPS_ROBOXCHANGE_TEMPLATE_TYPE_IFRAME'),
				]
			],
			'DEFAULT' => [
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => Sale\Handlers\PaySystem\RoboxchangeHandler::TEMPLATE_TYPE_CHECKOUT
			]
		],
		'ROBOXCHANGE_COUNTRY_CODE' => [
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_COUNTRY_CODE'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_COUNTRY_CODE_DESC'),
			'SORT' => 800,
			'GROUP' => 'CONNECT_SETTINGS_ROBOXCHANGE',
			'INPUT' => [
				'TYPE' => 'ENUM',
				'OPTIONS' => [
					'RU' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_COUNTRY_CODE_OPTION_RU'),
					'KZ' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_COUNTRY_CODE_OPTION_KZ'),
				]
			],
			'DEFAULT' => [
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => ($licensePrefix ?: $portalZone) === 'kz' ? 'KZ' : 'RU',
			]
		],
		'PAYMENT_ID' => [
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_PAYMENT_ID'),
			'SORT' => 900,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'ID',
				'PROVIDER_KEY' => 'PAYMENT'
			]
		],
		'BUYER_PERSON_EMAIL' => [
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_EMAIL_USER'),
			'SORT' => 1000,
			'GROUP' => 'BUYER_PERSON',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'EMAIL',
				'PROVIDER_KEY' => 'PROPERTY'
			]
		],
		'PS_CHANGE_STATUS_PAY' => [
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_CHANGE_STATUS_PAY'),
			'SORT' => 1100,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
			'DEFAULT' => [
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => 'Y',
			]
		],
		'PS_IS_TEST' => [
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_TEST'),
			'SORT' => 1200,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => [
				'TYPE' => 'Y/N'
			]
		],
	]
];
