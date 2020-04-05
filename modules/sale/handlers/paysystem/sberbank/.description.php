<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_SBERBANK_TITLE'),
	'CODES' => array(
		"SELLER_COMPANY_NAME" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_COMPANY_NAME_DESC'),
			"SORT" => 100,
			'GROUP' => 'SELLER_COMPANY',
		),
		"SELLER_COMPANY_INN" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_INN_DESC'),
			"SORT" => 200,
			'GROUP' => 'SELLER_COMPANY',
		),
		"SELLER_COMPANY_KPP" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_KPP_DESC'),
			"SORT" => 300,
			'GROUP' => 'SELLER_COMPANY',
		),
		"SELLER_COMPANY_BANK_ACCOUNT" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_SETTLEMENT_ACC_DESC'),
			"SORT" => 400,
			'GROUP' => 'SELLER_COMPANY'
		),
		"SELLER_COMPANY_BANK_NAME" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_BANK_NAME_DESC'),
			"SORT" => 500,
			'GROUP' => 'SELLER_COMPANY',
		),
		"SELLER_COMPANY_BANK_BIC" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_BANK_BIC_DESC'),
			"SORT" => 600,
			'GROUP' => 'SELLER_COMPANY',
		),
		"SELLER_COMPANY_BANK_ACCOUNT_CORR" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_BANK_COR_ACC_DESC'),
			"SORT" => 700,
			'GROUP' => 'SELLER_COMPANY',
		),
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_PAYMENT_ID_DESC'),
			"SORT" => 800,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'ACCOUNT_NUMBER'
			)
		),
		"PAYMENT_ORDER_ID" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_ORDER_ID_DESC'),
			"SORT" => 800,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'ORDER',
				'PROVIDER_VALUE' => 'ACCOUNT_NUMBER'
			)
		),
		"PAYMENT_DATE_INSERT" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_DATA_INSERT_DESC'),
			"SORT" => 900,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'DATE_BILL'
			)
		),
		"BUYER_PERSON_FIO" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_FIO_DESC'),
			"SORT" => 1000,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_ZIP" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_PAYER_ZIP_CODE_DESC'),
			"SORT" => 1100,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_COUNTRY" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_PAYER_COUNTRY_DESC'),
			"SORT" => 1200,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_REGION" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_PAYER_REGION_DESC'),
			"SORT" => 1300,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_CITY" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_PAYER_CITY_DESC'),
			"SORT" => 1400,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_VILLAGE" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_PAYER_VILLAGE_DESC'),
			"SORT" => 1400,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_STREET" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_PAYER_STREET_DESC'),
			"SORT" => 1400,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_ADDRESS_FACT" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_PAYER_ADDRESS_FACT_DESC'),
			"SORT" => 1500,
			'GROUP' => 'BUYER_PERSON'
		),
		"BUYER_PERSON_BANK_ACCOUNT" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_PAYER_ACCOUNT_DESC'),
			"SORT" => 1550,
			'GROUP' => 'BUYER_PERSON'
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_SUM_DESC'),
			"SORT" => 1600,
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			),
			'GROUP' => 'PAYMENT'
		),
		"PAYMENT_CURRENCY" => array(
			"NAME" => Loc::getMessage('SALE_HPS_SBERBANK_CURRENCY_DESC'),
			"SORT" => 1700,
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'CURRENCY'
			),
			'GROUP' => 'PAYMENT'
		)
	)
);