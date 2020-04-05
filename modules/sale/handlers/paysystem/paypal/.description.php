<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$description = array(
	'RETURN' => Loc::getMessage('SALE_HPS_PAYPAL_DESC_RETURN'),
	'RESTRICTION' => Loc::getMessage('SALE_HPS_PAYPAL_DESC_RESTRICTION'),
	'COMMISSION' => Loc::getMessage('SALE_HPS_PAYPAL_DESC_COMMISSION')
);

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_NAME'),
	'SORT' => 1000,
	'CODES' => array(
		'PAYPAL_USER'  => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_USER'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
		),
		'PAYPAL_PWD'  => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_PWD'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
		),
		'PAYPAL_SIGNATURE'  => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_SIGNATURE'),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
		),
		'PAYMENT_ID' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_ORDER_ID'),
			'SORT' => 400,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'ID',
				'PROVIDER_KEY' => 'PAYMENT',
			)
		),
		'PAYMENT_DATE_INSERT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_DATE_INSERT'),
			'SORT' => 500,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'DATE_BILL',
				'PROVIDER_KEY' => 'PAYMENT',
			)
		),
		'PAYMENT_SHOULD_PAY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_SHOULD_PAY'),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'SUM',
				'PROVIDER_KEY' => 'PAYMENT',
			)
		),
		'PAYMENT_CURRENCY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_CURRENCY'),
			'SORT' => 700,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'CURRENCY',
				'PROVIDER_KEY' => 'PAYMENT',
			)
		),
		'PAYPAL_NOTIFY_URL' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_NOTIFY_URL'),
			'SORT' => 800,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'https://'.$_SERVER['HTTP_HOST'].'/bitrix/tools/sale_ps_result.php',
				'PROVIDER_KEY' => 'VALUE',
			)
		),
		'PS_IS_TEST' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_TEST'),
			'SORT' => 900,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
		'PAYPAL_SSL_ENABLE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_SSL_ENABLE'),
			'SORT' => 1000,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		'PAYPAL_BUTTON_SRC'  => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_BUTTON_SRC'),
			'SORT' => 1100,
			'GROUP' => 'PS_OTHER',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_PAYPAL_BUTTON_SRC_NAME_VALUE'),
				'PROVIDER_KEY' => 'VALUE',
			)
		),
		'PAYPAL_ON0'  => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_ON0'),
			'GROUP' => 'PS_OTHER',
			'SORT' => 1300,
		),
		'PAYPAL_ON1'  => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_ON1'),
			'GROUP' => 'PS_OTHER',
			'SORT' => 1400
		),
		'PAYPAL_BUSINESS' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_BUSINESS'),
			'SORT' => 1500,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => '',
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'PAYPAL_IDENTITY_TOKEN' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_IDENTITY_TOKEN'),
			'SORT' => 1600,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL'
		),
		'PAYPAL_RETURN' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_RETURN'),
			'SORT' => 1700,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'https://'.$_SERVER['HTTP_HOST'].'/personal/payment/success.php',
				'PROVIDER_KEY' => 'VALUE',
			)
		),
		'PAYPAL_LC' => array(
			'NAME' => Loc::getMessage('SALE_HPS_PAYPAL_LS'),
			'SORT' => 1800,
			'GROUP' => 'CONNECT_SETTINGS_PAYPAL',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'RU' => Loc::getMessage('SALE_HPS_PAYPAL_LC_RUSSIAN'),
					'DE' => Loc::getMessage('SALE_HPS_PAYPAL_LC_GERMAN'),
					'US' => Loc::getMessage('SALE_HPS_PAYPAL_LC_ENGLISH')
				),
			)
		),
	)
)
?>