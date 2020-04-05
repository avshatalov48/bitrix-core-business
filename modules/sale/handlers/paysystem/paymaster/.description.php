<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_PAYMASTER'),
	'SORT' => 500,
	'CODES' => array(
		"PAYMASTER_SHOP_ACCT" => array(
			"NAME" => GetMessage("SALE_HPS_PAYMASTER_NUMBER"),
			"SORT" => 100,
			'GROUP' => 'CONNECT_SETTINGS_PAYMASTER',
		),
		"PS_IS_TEST" => array(
			"NAME" => GetMessage("SALE_HPS_PAYMASTER_TEST"),
			"SORT" => 200,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
		"PAYMASTER_CNST_SECRET_KEY" => array(
			"NAME" => GetMessage("SALE_HPS_PAYMASTER_KEY"),
			"SORT" => 300,
			'GROUP' => 'CONNECT_SETTINGS_PAYMASTER'
		),
		"PAYMENT_ID" => array(
			"NAME" => GetMessage("SALE_HPS_PAYMASTER_ORDER_ID"),
			"SORT" => 500,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'ID'
			)
		),
		"PAYMENT_DATE_INSERT" => array(
			"NAME" => GetMessage("SALE_HPS_PAYMASTER_DATE"),
			"SORT" => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'DATE_BILL'
			)
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => GetMessage("SALE_HPS_PAYMASTER_SUMMA"),
			"SORT" => 700,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			)
		),
		"PAYMENT_CURRENCY" => array(
			"NAME" => GetMessage("SALE_HPS_PAYMASTER_CURRENCY"),
			"SORT" => 800,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'CURRENCY'
			)
		),
		"PAYMASTER_RESULT_URL" => array(
			"NAME" => GetMessage("SALE_HPS_PAYMASTER_URL"),
			"SORT" => 900,
			'GROUP' => 'CONNECT_SETTINGS_PAYMASTER'
		),
		"PAYMASTER_SUCCESS_URL" => array(
			"NAME" => GetMessage("SALE_HPS_PAYMASTER_URL_OK"),
			"SORT" => 1000,
			'GROUP' => 'CONNECT_SETTINGS_PAYMASTER'
		),
		"PAYMASTER_FAIL_URL" => array(
			"NAME" => GetMessage("SALE_HPS_PAYMASTER_URL_ERROR"),
			"SORT" => 1100,
			'GROUP' => 'CONNECT_SETTINGS_PAYMASTER'
		),
		"BUYER_PERSON_PHONE" => array(
			"NAME" => GetMessage("SALE_HPS_PAYMASTER_PHONE"),
			'GROUP' => 'BUYER_PERSON',
			"SORT" => 1200
		),
		"PAYMASTER_HASH_ALGO" => array(
			"NAME" => Loc::getMessage("SALE_HPS_PAYMASTER_HASH_ALGO"),
			"SORT" => 1250,
			"TYPE" => "SELECT",
			'GROUP' => 'CONNECT_SETTINGS_PAYMASTER',
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					"md5" => 'md5',
					"sha256" => 'sha256'
				)
			),
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => 'md5'
			)
		),
		"BUYER_PERSON_EMAIL" => array(
			"NAME" => GetMessage("SALE_HPS_PAYMASTER_MAIL"),
			'GROUP' => 'BUYER_PERSON',
			"SORT" => 1300
		)
	)
);