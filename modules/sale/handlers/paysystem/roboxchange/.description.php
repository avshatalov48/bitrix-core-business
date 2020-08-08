<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$licensePrefix = Loader::includeModule('bitrix24') ? \CBitrix24::getLicensePrefix() : "";
$portalZone = Loader::includeModule('intranet') ? CIntranetUtils::getPortalZone() : "";

if (Loader::includeModule("bitrix24"))
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

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_TITLE'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => array(
		'ROBOXCHANGE_SHOPLOGIN' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_SHOPLOGIN'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_ROBOXCHANGE',
		),
		'ROBOXCHANGE_SHOPPASSWORD' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_SHOPPASSWORD'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_ROBOXCHANGE',
		),
		'ROBOXCHANGE_SHOPPASSWORD2' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_SHOPPASSWORD2'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_ROBOXCHANGE',
		),
		'ROBOXCHANGE_ORDERDESCR' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_ORDERDESCR'),
			'SORT' => 400,
			'GROUP' => 'PAYMENT',
		),
		'ROBOXCHANGE_SHOPPASSWORD_TEST' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_TEST_SHOPPASSWORD'),
			'SORT' => 500,
			'GROUP' => 'CONNECT_SETTINGS_ROBOXCHANGE',
		),
		'ROBOXCHANGE_SHOPPASSWORD2_TEST' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_TEST_SHOPPASSWORD2'),
			'SORT' => 600,
			'GROUP' => 'CONNECT_SETTINGS_ROBOXCHANGE',
		),
		'PAYMENT_ID' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_PAYMENT_ID'),
			'SORT' => 700,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'ID',
				'PROVIDER_KEY' => 'PAYMENT'
			)
		),
		'PAYMENT_SHOULD_PAY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_SHOULD_PAY'),
			'SORT' => 800,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'SUM',
				'PROVIDER_KEY' => 'PAYMENT'
			)
		),
		'PAYMENT_CURRENCY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_CURRENCY'),
			'SORT' => 900,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'CURRENCY',
				'PROVIDER_KEY' => 'PAYMENT'
			)
		),
		'PAYMENT_DATE_INSERT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_DATE_INSERT'),
			'SORT' => 1000,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'DATE_BILL',
				'PROVIDER_KEY' => 'PAYMENT'
			)
		),
		'BUYER_PERSON_EMAIL' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_EMAIL_USER'),
			'SORT' => 1100,
			'GROUP' => 'BUYER_PERSON',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'EMAIL',
				'PROVIDER_KEY' => 'PROPERTY'
			)
		),
		'PS_CHANGE_STATUS_PAY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_CHANGE_STATUS_PAY'),
			'SORT' => 1200,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_KEY" => "INPUT",
				"PROVIDER_VALUE" => "Y",
			)
		),
		'PS_IS_TEST' => array(
			'NAME' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_TEST'),
			'SORT' => 1300,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
	)
);
