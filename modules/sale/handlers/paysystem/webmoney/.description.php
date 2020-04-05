<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_WEBMONEY'),
	'SORT' => 500,
	'CODES' => array(
		"WEBMONEY_SHOP_ACCT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_NUMBER"),
			"SORT" => 100,
			'GROUP' => 'CONNECT_SETTINGS_WEBMONEY',
		),
		"PS_IS_TEST" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_TEST"),
			"SORT" => 200,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
		"WEBMONEY_CNST_SECRET_KEY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_KEY"),
			"SORT" => 300,
			'GROUP' => 'CONNECT_SETTINGS_WEBMONEY'
		),
		"WEBMONEY_HASH_ALGO" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_HASH_ALGO"),
			"SORT" => 400,
			"TYPE" => "SELECT",
			'GROUP' => 'CONNECT_SETTINGS_WEBMONEY',
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					"md5" => 'md5',
					"sha256" => 'sha256'
				)
			)
		),
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_PAYMENT_ID"),
			"SORT" => 500,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'ID'
			)
		),
		"PAYMENT_DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_DATE"),
			"SORT" => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'DATE_BILL'
			)
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_SUMMA"),
			"SORT" => 700,
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			),
			'GROUP' => 'PAYMENT'
		),
		"WEBMONEY_RESULT_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_URL"),
			"SORT" => 800,
			'GROUP' => 'CONNECT_SETTINGS_WEBMONEY',
		),
		"WEBMONEY_SUCCESS_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_URL_OK"),
			"SORT" => 900,
			'GROUP' => 'CONNECT_SETTINGS_WEBMONEY',
		),
		"WEBMONEY_FAIL_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_URL_ERROR"),
			"SORT" => 1000,
			'GROUP' => 'CONNECT_SETTINGS_WEBMONEY',
		),
		"BUYER_PERSON_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_PHONE"),
			'GROUP' => 'BUYER_PERSON',
			"SORT" => 1100,
			"DEFAULT" => array(
				'PROVIDER_KEY' => 'PROPERTY',
				'PROVIDER_VALUE' => 'PHONE'
			)
		),
		"BUYER_PERSON_EMAIL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_MAIL"),
			'GROUP' => 'BUYER_PERSON',
			"SORT" => 1200,
			"DEFAULT" => array(
				'PROVIDER_KEY' => 'PROPERTY',
				'PROVIDER_VALUE' => 'EMAIL'
			)
		),
		"PS_CHANGE_STATUS_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_WEBMONEY_CHANGE_STATUS_PAY"),
			"SORT" => 1300,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		)
	)
);