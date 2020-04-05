<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$isAvailable = \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$licensePrefix = \Bitrix\Main\Loader::includeModule("bitrix24") ? \CBitrix24::getLicensePrefix() : "";
if (IsModuleInstalled("bitrix24") && !in_array($licensePrefix, ["ru"]))
{
	$isAvailable = \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_ASSIST'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => array(
		"ASSIST_SHOP_IDP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_SHOP_IDP"),
			"SORT" => 100,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		),
		"ASSIST_SHOP_LOGIN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_SHOP_LOGIN"),
			"SORT" => 200,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		),
		"ASSIST_SHOP_PASSWORD" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_SHOP_PASSWORD"),
			"SORT" => 300,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		),
		"ASSIST_SERVER_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_SERVER_URL"),
			"SORT" => 350,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 'payments.paysecure.ru',
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"ASSIST_SHOP_SECRET_WORLD" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_SHOP_SECRET_WORLD"),
			"SORT" => 400,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		),
		"ASSIST_DELAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_ASSIST_DELAY"),
			"SORT" => 500,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_SHOULD_PAY"),
			"SORT" => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			)
		),
		"PAYMENT_CURRENCY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_CURRENCY"),
			"SORT" => 700,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'CURRENCY'
			)
		),
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_ORDER_ID"),
			"SORT" => 800,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'ID'
			)
		),
		"PAYMENT_DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_DATE_INSERT"),
			"SORT" => 1000,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'DATE_BILL'
			)
		),
		"ASSIST_SUCCESS_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_SUCCESS_URL"),
			"SORT" => 1100,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST',
		),
		"ASSIST_FAIL_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_FAIL_URL"),
			"SORT" => 1200,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST',
		),
		"BUYER_PERSON_NAME_FIRST" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_FIRST_NAME"),
			"SORT" => 1300,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_NAME_SECOND" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_MIDDLE_NAME"),
			"SORT" => 1400,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_NAME_LAST" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_LAST_NAME"),
			"SORT" => 1500,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_EMAIL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_EMAIL"),
			"SORT" => 1600,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_ADDRESS" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_ADDRESS"),
			"SORT" => 1700,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_PHONE"),
			"SORT" => 1800,
			'GROUP' => 'BUYER_PERSON'
		),
		"ASSIST_PAYMENT_CardPayment" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_PAYMENT_CardPayment"),
			"SORT" => 1900,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		),
		"ASSIST_PAYMENT_YMPayment" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_PAYMENT_YMPayment"),
			"SORT" => 2000,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		),
		"ASSIST_PAYMENT_WebMoneyPayment" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_PAYMENT_WebMoneyPayment"),
			"SORT" => 2100,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		),
		"ASSIST_PAYMENT_QIWIPayment" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_PAYMENT_QIWIPayment"),
			"SORT" => 2200,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		),
		"ASSIST_PAYMENT_AssistIDCCPayment" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_PAYMENT_AssistIDCCPayment"),
			"SORT" => 2300,
			'GROUP' => 'CONNECT_SETTINGS_ASSIST'
		),
		"PS_CHANGE_STATUS_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_AUTOPAY"),
			"SORT" => 2400,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
		"PS_IS_TEST" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ASSIST_DEMO"),
			"SORT" => 2500,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		)
	)
);