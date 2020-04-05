<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => Loc::getMessage("SALE_HPS_BILL_UA_DTITLE"),
	'SORT' => 100,
	'CODES' => array(
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_ORDER_ID"),
			'SORT' => 100,
			'GROUP' => 'PAYMENT',
			"DEFAULT" => array(
				"VALUE" => "ID",
				"TYPE" => "PAYMENT"
			)
		),
		"DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_DATE"),
			'SORT' => 200,
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_DATE_DESC"),
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				"VALUE" => "DATE_BILL_DATE",
				"TYPE" => "PAYMENT"
			)
		),
		"DATE_PAY_BEFORE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_PAY_BEFORE"),
			'SORT' => 300,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				"VALUE" => "DATE_PAY_BEFORE",
				"TYPE" => "ORDER"
			)
		),
		"BILLUA_SELLER_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_SELLER_SHOW"),
			'SORT' => 350,
			'GROUP' => 'SELLER_COMPANY',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"SELLER_COMPANY_NAME" => array(
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 400,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_BANK_ACCOUNT" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_ORDER_SUPPLI"),
			'SORT' => 500,
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_ORDER_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_BANK_NAME" => array(
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 600,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_ORDER_BANK"),
		),
		"SELLER_COMPANY_MFO" => array(
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 700,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_ORDER_MFO"),
		),
		"SELLER_COMPANY_ADDRESS" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_ADRESS_SUPPLI"),
			'SORT' => 800,
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_ADRESS_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_PHONE" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_PHONE_SUPPLI"),
			'SORT' => 900,
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_PHONE_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_EDRPOY" => array(
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1000,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_EDRPOY_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_EDRPOY_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_IPN" => array(
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1100,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_IPN_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_IPN_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_PDV" => array(
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1200,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_PDV_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_PDV_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_SYS" => array(
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1300,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_SYS_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_SYS_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_ACCOUNTANT_NAME" => array(
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1400,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_ACC_SUPPLI"),
		),
		"SELLER_COMPANY_ACCOUNTANT_POSITION" => array(
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1500,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_ACC_POS_SUPPLI"),
		),
		"BILLUA_PAYER_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_PAYER_SHOW"),
			'SORT' => 1550,
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
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'SORT' => 1600,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_CUSTOMER"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_CUSTOMER_DESC"),
			"VALUE" => "COMPANY_NAME",
			"TYPE" => "PROPERTY"
		),
		"BUYER_PERSON_COMPANY_ADDRESS" => array(
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'SORT' => 1700,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_CUSTOMER_ADRES"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_CUSTOMER_ADRES_DESC"),
			'DEFAULT' => array(
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_FAX" => array(
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'SORT' => 1800,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_CUSTOMER_FAX"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_CUSTOMER_FAX_DESC"),
		),
		"BUYER_PERSON_COMPANY_PHONE" => array(
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'SORT' => 1900,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_CUSTOMER_PHONE"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_CUSTOMER_PHONE_DESC"),
			'DEFAULT' => array(
				"VALUE" => "PHONE",
				"TYPE" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_DOGOVOR" => array(
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'SORT' => 2000,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_CUSTOMER_DOGOVOR"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_CUSTOMER_DOGOVOR"),
		),
		"BILLUA_HEADER" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_HEADER"),
			'SORT' => 2010,
			'GROUP' => 'GENERAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_UA_HEADER_VALUE'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_TOTAL_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_TOTAL_SHOW"),
			'SORT' => 2020,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLUA_FOOTER_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_FOOTER_SHOW"),
			'SORT' => 2030,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLUA_COMMENT1" => array(
			'GROUP' => 'GENERAL_SETTINGS',
			'SORT' => 2100,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COMMENT1"),
			'DEFAULT' => array(
				"VALUE" => Loc::getMessage("SALE_HPS_BILL_UA_COMMENT1_VALUE"),
				"TYPE" => 'VALUE'
			)
		),
		"BILLUA_COMMENT2" => array(
			'GROUP' => 'GENERAL_SETTINGS',
			'SORT' => 2200,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COMMENT2")
		),
		"BILLUA_PATH_TO_STAMP" => array(
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 2300,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_PRINT"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_PRINT_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"SELLER_COMPANY_ACC_SIGN" => array(
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 2400,
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_ACC_SIGN_SUPPLI"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLUA_BACKGROUND" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_BACKGROUND"),
			'SORT' => 2500,
			'GROUP' => 'VISUAL_SETTINGS',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_UA_BACKGROUND_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLUA_BACKGROUND_STYLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_BACKGROUND_STYLE"),
			'SORT' => 2600,
			'GROUP' => 'VISUAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'none' => Loc::getMessage("SALE_HPS_BILL_UA_BACKGROUND_STYLE_NONE"),
					'tile' => Loc::getMessage("SALE_HPS_BILL_UA_BACKGROUND_STYLE_TILE"),
					'stretch' => Loc::getMessage("SALE_HPS_BILL_UA_BACKGROUND_STYLE_STRETCH")
				)
			),
			"TYPE" => "SELECT"
		),
		"BILLUA_MARGIN_TOP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_MARGIN_TOP"),
			'SORT' => 2700,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				"VALUE" => "15",
				"TYPE" => 'VALUE'
			)
		),
		"BILLUA_MARGIN_RIGHT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_MARGIN_RIGHT"),
			'SORT' => 2800,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				"VALUE" => "15",
				"TYPE" => 'VALUE'
			)
		),
		"BILLUA_MARGIN_BOTTOM" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_MARGIN_BOTTOM"),
			'SORT' => 2900,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				"VALUE" => "15",
				"TYPE" => 'VALUE'
			)
		),
		"BILLUA_MARGIN_LEFT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_MARGIN_LEFT"),
			'SORT' => 3000,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				"VALUE" => "20",
				"TYPE" => 'VALUE'
			)
		),
		"BILLUA_COLUMN_NUMBER_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_NUMBER_TITLE"),
			'SORT' => 3100,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_NUMBER_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_NUMBER_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SORT"),
			'SORT' => 3150,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 100,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_NUMBER_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SHOW"),
			'SORT' => 3200,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLUA_COLUMN_NAME_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_NAME_TITLE"),
			'SORT' => 3300,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_NAME_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_NAME_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SORT"),
			'SORT' => 3350,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 200,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_NAME_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SHOW"),
			'SORT' => 3400,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLUA_COLUMN_QUANTITY_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_QUANTITY_TITLE"),
			'SORT' => 3500,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_QUANTITY_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_QUANTITY_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SORT"),
			'SORT' => 3550,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 300,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_QUANTITY_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SHOW"),
			'SORT' => 3600,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLUA_COLUMN_MEASURE_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_MEASURE_TITLE"),
			'SORT' => 3700,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_UA_COLUMN_MEASURE_VALUE'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_MEASURE_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SORT"),
			'SORT' => 3750,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 400,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_MEASURE_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SHOW"),
			'SORT' => 3800,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLUA_COLUMN_PRICE_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_PRICE_TITLE"),
			'SORT' => 3900,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_PRICE_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_PRICE_TAX_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_PRICE_TAX_TITLE"),
			'SORT' => 3920,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_PRICE_TAX_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_PRICE_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SORT"),
			'SORT' => 3950,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 500,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_PRICE_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SHOW"),
			'SORT' => 4000,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLUA_COLUMN_VAT_RATE_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_VAT_RATE_TITLE"),
			'SORT' => 4100,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_UA_COLUMN_VAT_RATE_VALUE'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_VAT_RATE_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SORT"),
			'SORT' => 4150,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 600,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_VAT_RATE_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SHOW"),
			'SORT' => 4200,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLUA_COLUMN_SUM_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SUM_TITLE"),
			'SORT' => 4300,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SUM_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_SUM_TAX_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SUM_TAX_TITLE"),
			'SORT' => 4400,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SUM_TAX_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_SUM_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SORT"),
			'SORT' => 4450,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 700,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLUA_COLUMN_SUM_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_UA_COLUMN_SHOW"),
			'SORT' => 4500,
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