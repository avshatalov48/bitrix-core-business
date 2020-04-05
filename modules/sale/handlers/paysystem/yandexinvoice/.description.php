<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_YANDEX_INVOICE'),
	'SORT' => 500,
	'IS_AVAILABLE' => \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_FALSE,
	'CODES' => array(
		"YANDEX_INVOICE_SHOP_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_INVOICE_SHOP_ID"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_INVOICE_SHOP_ID_DESC"),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX_INVOICE',
		),
		"YANDEX_INVOICE_SHOP_ARTICLE_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_INVOICE_SHOP_ARTICLE_ID"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_INVOICE_SHOP_ARTICLE_ID_DESC"),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX_INVOICE',
		),
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_INVOICE_PAYMENT_ID"),
			'SORT' => 400,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'ACCOUNT_NUMBER'
			)
		),
		"PAYMENT_DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_INVOICE_PAYMENT_DATE"),
			'SORT' => 500,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'DATE_BILL'
			)
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_INVOICE_SHOULD_PAY"),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			)
		),
		"PS_CHANGE_STATUS_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_INVOICE_CHANGE_STATUS_PAY"),
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
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_INVOICE_IS_TEST"),
			'SORT' => 900,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
		"BUYER_PERSON_COMPANY_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_INVOICE_BUYER_PHONE"),
			'SORT' => 1000,
			'GROUP' => 'GENERAL_SETTINGS',
		),
	)
);