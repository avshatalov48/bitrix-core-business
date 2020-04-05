<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem;


Loc::loadMessages(__FILE__);

$description = array(
	'RETURN' => Loc::getMessage('SALE_HPS_YANDEX_RETURN'),
	'RESTRICTION' => Loc::getMessage('SALE_HPS_YANDEX_RESTRICTION'),
	'COMMISSION' => Loc::getMessage('SALE_HPS_YANDEX_COMMISSION'),
	'MAIN' => Loc::getMessage('SALE_HPS_YANDEX_DESCRIPTION')
);

if (IsModuleInstalled('bitrix24'))
{
	$description['REFERRER'] = Loc::getMessage('SALE_HPS_YANDEX_REFERRER');
}

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$portalZone = Loader::includeModule('intranet') ? CIntranetUtils::getPortalZone() : "";
$licensePrefix = Loader::includeModule('bitrix24') ? \CBitrix24::getLicensePrefix() : "";

if (
	(Loader::includeModule('intranet') && $portalZone !== 'ru')
	|| (Loader::includeModule("bitrix24") && $licensePrefix !== 'ru')
)
{
	$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_YANDEX'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => array(
		"YANDEX_SHOP_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_SHOP_ID"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_SHOP_ID_DESC"),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
		),
		"YANDEX_SCID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_SCID"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_SCID_DESC"),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
		),
		"YANDEX_SHOP_KEY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_SHOP_KEY"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_SHOP_KEY_DESC"),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
		),
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_PAYMENT_ID"),
			'SORT' => 400,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'ACCOUNT_NUMBER'
			)
		),
		"PAYMENT_DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_PAYMENT_DATE"),
			'SORT' => 500,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'DATE_BILL'
			)
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_SHOULD_PAY"),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			)
		),
		"PS_CHANGE_STATUS_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_CHANGE_STATUS_PAY"),
			'SORT' => 700,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_KEY" => "INPUT",
				"PROVIDER_VALUE" => "Y",
			)
		),
		"PS_IS_TEST" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_IS_TEST"),
			'SORT' => 900,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
		"PAYMENT_BUYER_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_BUYER_ID"),
			'SORT' => 1000,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'ORDER',
				'PROVIDER_VALUE' => 'USER_ID'
			)
		),
	)
);