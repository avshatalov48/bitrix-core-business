<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) 
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_BILL_BY_TITLE'),
	'SORT' => 100,
	'CODES' => array(
		"PAYMENT_DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_DATE"),
			"SORT" => 100,
			'GROUP' => 'PAYMENT',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_DATE_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "DATE_BILL_DATE",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"PAYMENT_DATE_PAY_BEFORE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_PAY_BEFORE"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_PAY_BEFORE_DESC"),
			"SORT" => 300,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "DATE_PAY_BEFORE",
				"PROVIDER_KEY" => "ORDER"
			)
		),
		"SELLER_COMPANY_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_SUPPLI_DESC"),
			"SORT" => 400
		),
		"BILLBY_SIGN_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_SIGN_SHOW"),
			'SORT' => 310,
			'GROUP' => 'SELLER_COMPANY',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"SELLER_COMPANY_ADDRESS" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_ADRESS_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_ADRESS_SUPPLI_DESC"),
			"SORT" => 500
		),
		"SELLER_COMPANY_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_PHONE_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_PHONE_SUPPLI_DESC"),
			"SORT" => 600
		),
		"SELLER_COMPANY_INN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_INN_SUPPLI"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_BY_INN_SUPPLI_SHORT"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_INN_SUPPLI_DESC"),
			"SORT" => 700
		),
		"SELLER_COMPANY_BANK_ACCOUNT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_ORDER_SUPPLI"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_BY_ORDER_SUPPLI_SHORT"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_ORDER_SUPPLI_DESC"),
			"SORT" => 800,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_BY_ORDER_SUPPLI_VAL"),
				"PROVIDER_KEY" => ""
			)
		),
		"SELLER_COMPANY_BANK_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_BANK_SUPPLI"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_BY_BANK_SUPPLI_SHORT"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_BANK_SUPPLI_DESC"),
			"SORT" => 900,
		),
		"SELLER_COMPANY_BANK_CITY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_BCITY_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_BCITY_SUPPLI_DESC"),
			"SORT" => 1000
		),
		"SELLER_COMPANY_BANK_BIC" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_BIK_SUPPLI"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_BY_BIK_SUPPLI_SHORT"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_BIK_SUPPLI_DESC"),
			"SORT" => 1100
		),
		"SELLER_COMPANY_DIRECTOR_POSITION" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_DIR_POS_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_DIR_POS_SUPPLI_DESC"),
			"SORT" => 1200,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_BY_DIR_POS_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_ACCOUNTANT_POSITION" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_ACC_POS_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_ACC_POS_SUPPLI_DESC"),
			'GROUP' => 'SELLER_COMPANY',
			"SORT" => 1300,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_BY_ACC_POS_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_DIRECTOR_NAME" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_DIR_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_DIR_SUPPLI_DESC"),
			"SORT" => 1400
		),
		"SELLER_COMPANY_ACCOUNTANT_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_ACC_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_ACC_SUPPLI_DESC"),
			"SORT" => 1500
		),
		"BILLBY_PAYER_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_PAYER_SHOW"),
			'SORT' => 1610,
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BUYER_PERSON_COMPANY_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_DESC"),
			"SORT" => 1600,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "COMPANY_NAME",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_INN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_INN"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_INN_DESC"),
			"SORT" => 1700,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "INN",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_BANK_ACCOUNT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_ORDER_CUSTOMER"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_BY_ORDER_CUSTOMER_SHORT"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_ORDER_CUSTOMER_DESC"),
			"SORT" => 1800,
		),
		"BUYER_PERSON_COMPANY_BANK_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_BANK_CUSTOMER"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_BY_BANK_CUSTOMER_SHORT"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_BANK_CUSTOMER_DESC"),
			"SORT" => 1900,
		),
		"BUYER_PERSON_COMPANY_BANK_CITY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_BCITY_CUSTOMER"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_BCITY_CUSTOMER_DESC"),
			"SORT" => 2000
		),
		"BUYER_PERSON_COMPANY_BANK_BIC" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_BIK_CUSTOMER"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_BY_BIK_CUSTOMER_SHORT"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_BIK_CUSTOMER_DESC"),
			"SORT" => 2100
		),
		"BUYER_PERSON_COMPANY_ADDRESS" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_ADRES"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_ADRES_DESC"),
			"SORT" => 2200,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "ADDRESS",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_PHONE"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_PHONE_DESC"),
			"SORT" => 2300,
			"PROVIDER_VALUE" => "PHONE",
			"PROVIDER_KEY" => "PROPERTY"
		),
		"BUYER_PERSON_COMPANY_FAX" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_FAX"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_FAX_DESC"),
			"SORT" => 2400,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "FAX",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_NAME_CONTACT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_PERSON"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_PERSON_DESC"),
			"SORT" => 2500,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "PAYER_NAME",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_DOGOVOR" => array(
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'SORT' => 2600,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_DOGOVOR"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_CUSTOMER_DOGOVOR_DESC"),
		),
		"BILLBY_HEADER" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_HEADER"),
			'SORT' => 2610,
			'GROUP' => 'GENERAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_BY_HEADER_VALUE'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_ORDER_SUBJECT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_ORDER_SUBJECT"),
			'GROUP' => 'GENERAL_SETTINGS',
			"SORT" => 2620
		),
		"BILLBY_HEADER_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_HEADER_SHOW"),
			'SORT' => 2630,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLBY_TOTAL_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_TOTAL_SHOW"),
			'SORT' => 2640,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLBY_COMMENT1" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COMMENT1"),
			"SORT" => 2700,
			'GROUP' => 'GENERAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_BY_COMMENT1_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COMMENT2" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COMMENT2"),
			"SORT" => 2800,
			'GROUP' => 'GENERAL_SETTINGS',
		),
		"BILLBY_PATH_TO_LOGO" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_LOGO"),
			"SORT" => 2900,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_LOGO_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLBY_LOGO_DPI" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_LOGO_DPI"),
			"SORT" => 3000,
			'GROUP' => 'SELLER_COMPANY',
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'96' => Loc::getMessage("SALE_HPS_BILL_BY_LOGO_DPI_96"),
					'600' => Loc::getMessage("SALE_HPS_BILL_BY_LOGO_DPI_600"),
					'300' => Loc::getMessage("SALE_HPS_BILL_BY_LOGO_DPI_300"),
					'150' => Loc::getMessage("SALE_HPS_BILL_BY_LOGO_DPI_150"),
					'72' => Loc::getMessage("SALE_HPS_BILL_BY_LOGO_DPI_72")
				)
			),
		),
		"BILLBY_PATH_TO_STAMP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_PRINT"),
			"SORT" => 3100,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_PRINT_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"SELLER_COMPANY_DIR_SIGN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_DIR_SIGN_SUPPLI"),
			"SORT" => 3200,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_DIR_SIGN_SUPPLI_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"SELLER_COMPANY_ACC_SIGN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_ACC_SIGN_SUPPLI"),
			"SORT" => 3300,
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_ACC_SIGN_SUPPLI_DESC"),
			'GROUP' => 'SELLER_COMPANY',
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLBY_BACKGROUND" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_BACKGROUND"),
			"SORT" => 3400,
			'GROUP' => 'VISUAL_SETTINGS',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BY_BACKGROUND_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLBY_BACKGROUND_STYLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_BACKGROUND_STYLE"),
			"SORT" => 3500,
			'GROUP' => 'VISUAL_SETTINGS',
			'TYPE' => 'SELECT',
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'none' => Loc::getMessage("SALE_HPS_BILL_BY_BACKGROUND_STYLE_NONE"),
					'tile' => Loc::getMessage("SALE_HPS_BILL_BY_BACKGROUND_STYLE_TILE"),
					'stretch' => Loc::getMessage("SALE_HPS_BILL_BY_BACKGROUND_STYLE_STRETCH")
				)
			),
		),
		"BILLBY_MARGIN_TOP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_MARGIN_TOP"),
			"SORT" => 3600,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "15",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_MARGIN_RIGHT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_MARGIN_RIGHT"),
			"SORT" => 3700,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "15",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_MARGIN_BOTTOM" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_MARGIN_BOTTOM"),
			"SORT" => 3800,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "15",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_MARGIN_LEFT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_MARGIN_LEFT"),
			"SORT" => 3900,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "20",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_NUMBER_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_NUMBER_TITLE"),
			'SORT' => 4400,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_NUMBER_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_NUMBER_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SORT"),
			'SORT' => 4500,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 100,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_NUMBER_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SHOW"),
			'SORT' => 4600,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLBY_COLUMN_NAME_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_NAME_TITLE"),
			'SORT' => 4700,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_NAME_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_NAME_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SORT"),
			'SORT' => 4800,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 200,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_NAME_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SHOW"),
			'SORT' => 4900,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLBY_COLUMN_QUANTITY_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_QUANTITY_TITLE"),
			'SORT' => 5000,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_QUANTITY_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_QUANTITY_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SORT"),
			'SORT' => 5100,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 300,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_QUANTITY_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SHOW"),
			'SORT' => 5200,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLBY_COLUMN_MEASURE_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_MEASURE_TITLE"),
			'SORT' => 5300,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_BY_COLUMN_MEASURE_VALUE'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_MEASURE_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SORT"),
			'SORT' => 5400,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 400,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_MEASURE_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SHOW"),
			'SORT' => 5500,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLBY_COLUMN_PRICE_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_PRICE_TITLE"),
			'SORT' => 5600,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_PRICE_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_PRICE_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SORT"),
			'SORT' => 5700,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 500,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_PRICE_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SHOW"),
			'SORT' => 5800,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLBY_COLUMN_SUM_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SUM_TITLE"),
			'SORT' => 5900,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SUM_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_SUM_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SORT"),
			'SORT' => 6000,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 600,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_SUM_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SHOW"),
			'SORT' => 6100,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLBY_COLUMN_VAT_RATE_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_VAT_RATE_TITLE"),
			'SORT' => 6200,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_BY_COLUMN_VAT_RATE_VALUE'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_VAT_RATE_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SORT"),
			'SORT' => 6300,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 700,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_VAT_RATE_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SHOW"),
			'SORT' => 6400,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLBY_COLUMN_VAT_SUM_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_VAT_SUM_TITLE"),
			'SORT' => 6500,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_BY_COLUMN_VAT_SUM_VALUE'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_VAT_SUM_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SORT"),
			'SORT' => 6600,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 800,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_VAT_SUM_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SHOW"),
			'SORT' => 6700,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLBY_COLUMN_TOTAL_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_TOTAL_TITLE"),
			'SORT' => 6800,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_BY_COLUMN_TOTAL_VALUE'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_TOTAL_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SORT"),
			'SORT' => 6900,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 900,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLBY_COLUMN_TOTAL_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BY_COLUMN_SHOW"),
			'SORT' => 7000,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
	)
);
?>