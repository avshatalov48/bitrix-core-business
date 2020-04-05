<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) 
	die();

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_ALFABANK_TITLE'),
	'SORT' => 100,
	'IS_AVAILABLE' => PaySystem\Manager::HANDLER_AVAILABLE_FALSE,
	'CODES' => array(
		"ALFABANK_EXTERNAL_SYSTEM_CODE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_EXT_SYSTEM_CODE"),
			"SORT" => 100,
			'GROUP' => 'CONNECT_SETTINGS_ALFABANK',
		),
		"ALFABANK_PAYMENT_SUBJECT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_PAYMENT_SUBJECT"),
			'GROUP' => 'CONNECT_SETTINGS_ALFABANK',
			"SORT" => 200
		),
		"ALFABANK_EXTERNAL_USER_CODE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_EXT_USER_CODE"),
			"SORT" => 200,
			'GROUP' => 'CONNECT_SETTINGS_ALFABANK',
		),
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_PAYMENT_ID"),
			"SORT" => 300,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "ID",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"PAYMENT_DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_DATE"),
			"SORT" => 400,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "DATE_BILL",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_SHOULD_PAY"),
			'SORT' => 500,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			)
		),
		"SELLER_COMPANY_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_COMPANY_NAME"),
			'GROUP' => 'SELLER_COMPANY',
			"SORT" => 600
		),
		"SELLER_COMPANY_INN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_INN_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"SORT" => 700
		),
		"SELLER_COMPANY_KPP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_KPP_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"SORT" => 800,
		),
		"SELLER_COMPANY_BANK_ACCOUNT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_ACC_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"SORT" => 900
		),
		"SELLER_COMPANY_BANK_BIC" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_BIK_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"SORT" => 1000
		),
		"BUYER_PERSON_COMPANY_INN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_CUSTOMER_INN"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 1100,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "INN",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_KPP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_CUSTOMER_KPP"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 1200,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "KPP",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_NAME_CONTACT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_CUSTOMER_PERSON"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 1300,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "PAYER_NAME",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"ALFABANK_PRIORITY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_PRIORITY"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 1500,
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					"1" => 1,
					"2" => 2,
					"3" => 3,
					"4" => 4,
					"5" => 5,
					"6" => 6
				)
			)
		),
		"ALFABANK_BUDGET_OKATO" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_BUDGET_OKATO"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 1600
		),
		"ALFABANK_BUDGET_PAYER_STATUS" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_BUDGET_PAYER_STATUS"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 1700
		),
		"ALFABANK_BUDGET_KBK" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_BUDGET_KBK"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 1800
		),
		"ALFABANK_BUDGET_OKTMO" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_BUDGET_OKTMO"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 1900
		),
		"ALFABANK_BUDGET_PAYMENT_BASE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_BUDGET_PAYMENT_BASE"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 2000
		),
		"ALFABANK_BUDGET_PERIOD" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_BUDGET_PERIOD"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 2100
		),
		"ALFABANK_BUDGET_DOC_NUMBER" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_BUDGET_DOC_NUMBER"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 2200
		),
		"ALFABANK_BUDGET_DOC_DATE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_ALFABANK_BUDGET_DOC_DATE"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 2300
		),
		"ALFABANK_BUDGET_PAYMENT_TYPE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_ALFABANK_BUDGET_PAYMENT_TYPE"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"SORT" => 2400
		)
	)
);
?>