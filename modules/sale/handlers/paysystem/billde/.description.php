<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) 
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
Loc::loadLanguageFile(__FILE__, 'de');

$data = array(
	'NAME' => Loc::getMessage("SALE_HPS_BILL_DE_TITLE"),
	'SORT' => 1800,
	'CODES' => array(
		"DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_DATE"),
			'SORT' => 100,
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_DATE_DESC"),
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "DATE_BILL_DATE",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"DATE_PAY_BEFORE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_PAY_BEFORE"),
			'SORT' => 200,
			'GROUP' => 'PAYMENT',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_PAY_BEFORE_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "DATE_PAY_BEFORE",
				"PROVIDER_KEY" => "ORDER"
			)
		),
		"SELLER_COMPANY_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_SUPPLI"),
			'SORT' => 300,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_SUPPLI_DESC")
		),
		"SELLER_COMPANY_ADDRESS" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_ADRESS_SUPPLI"),
			'SORT' => 400,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_ADRESS_SUPPLI_DESC")
		),
		"SELLER_COMPANY_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_PHONE_SUPPLI"),
			'SORT' => 500,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_PHONE_SUPPLI_DESC")
		),
		"SELLER_COMPANY_EMAIL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_EMAIL_SUPPLI"),
			'SORT' => 600,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_EMAIL_SUPPLI_DESC")
		),
		"SELLER_COMPANY_BANK_ACCOUNT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_ACCNO_SUPPLI"),
			'SORT' => 700,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_ACCNO_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_ACCNO_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_BANK_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_SUPPLI"),
			'SORT' => 800,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_BANK_BIC" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_BLZ_SUPPLI"),
			'SORT' => 900,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_BLZ_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_BLZ_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_BANK_IBAN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_IBAN_SUPPLI"),
			'SORT' => 1000,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_IBAN_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_IBAN_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_BANK_SWIFT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_SWIFT_SUPPLI"),
			'SORT' => 1100,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_SWIFT_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_SWIFT_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_EU_INN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_EU_INN_SUPPLI"),
			'SORT' => 1200,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_EU_INN_SUPPLI_DESC")
		),
		"SELLER_COMPANY_INN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_INN_SUPPLI"),
			'SORT' => 1300,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_INN_SUPPLI_DESC")
		),
		"SELLER_COMPANY_REG" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_REG_SUPPLI"),
			'SORT' => 1400,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_REG_SUPPLI_DESC")
		),
		"SELLER_COMPANY_DIRECTOR_POSITION" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_POS_SUPPLI"),
			'SORT' => 1500,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_POS_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_POS_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_ACCOUNTANT_POSITION" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_POS_SUPPLI"),
			'SORT' => 1600,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_POS_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_POS_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_DIRECTOR_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_SUPPLI"),
			'SORT' => 1700,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_SUPPLI_DESC")
		),
		"SELLER_COMPANY_ACCOUNTANT_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_SUPPLI"),
			'SORT' => 1800,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_SUPPLI_DESC")
		),
		"BUYER_PERSON_COMPANY_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_ID"),
			'SORT' => 1900,
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "USER_ID",
				"PROVIDER_KEY" => "ORDER"
			)
		),
		"BUYER_PERSON_COMPANY_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER"),
			'SORT' => 2000,
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "COMPANY_NAME",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_ADDRESS" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_ADRES"),
			'SORT' => 2100,
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_ADRES_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "ADDRESS",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_PAYER_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_PERSON"),
			'SORT' => 2400,
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_PERSON_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "PAYER_NAME",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BILLDE_HEADER" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_HEADER"),
			'SORT' => 2410,
			'GROUP' => 'GENERAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_DE_HEADER_VALUE', null, 'de'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_HEADER_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_HEADER_SHOW"),
			'SORT' => 2420,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLDE_TOTAL_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_TOTAL_SHOW"),
			'SORT' => 2430,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLDE_COMMENT1" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COMMENT1"),
			'SORT' => 2500,
			'GROUP' => 'GENERAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_COMMENT1_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COMMENT2" => array(
			'SORT' => 2600,
			'GROUP' => 'GENERAL_SETTINGS',
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COMMENT2")
		),
		"BILLDE_PATH_TO_LOGO" => array(
			'SORT' => 2700,
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_LOGO"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_LOGO_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLDE_LOGO_DPI" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_LOGO_DPI"),
			'SORT' => 2800,
			'GROUP' => 'SELLER_COMPANY',
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'96' => Loc::getMessage('SALE_HPS_BILL_DE_LOGO_DPI_96'),
					'600' => Loc::getMessage('SALE_HPS_BILL_DE_LOGO_DPI_600'),
					'300' => Loc::getMessage('SALE_HPS_BILL_DE_LOGO_DPI_300'),
					'150' => Loc::getMessage('SALE_HPS_BILL_DE_LOGO_DPI_150'),
					'72' => Loc::getMessage('SALE_HPS_BILL_DE_LOGO_DPI_72')
				)
			)
		),
		"BILLDE_PATH_TO_STAMP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_PRINT"),
			'SORT' => 2900,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_PRINT_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"SELLER_COMPANY_DIR_SIGN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_SIGN_SUPPLI"),
			'SORT' => 3000,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_SIGN_SUPPLI_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"SELLER_COMPANY_ACC_SIGN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_SIGN_SUPPLI"),
			'SORT' => 3100,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_SIGN_SUPPLI_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLDE_BACKGROUND" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BACKGROUND"),
			'SORT' => 3200,
			'GROUP' => 'VISUAL_SETTINGS',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_BACKGROUND_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLDE_BACKGROUND_STYLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BACKGROUND_STYLE"),
			'SORT' => 3300,
			'GROUP' => 'VISUAL_SETTINGS',
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'tile' => Loc::getMessage('SALE_HPS_BILL_DE_BACKGROUND_STYLE_TILE'),
					'stretch' => Loc::getMessage('SALE_HPS_BILL_DE_BACKGROUND_STYLE_STRETCH')
				)
			)
		),
		"BILLDE_MARGIN_TOP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_MARGIN_TOP"),
			'SORT' => 3400,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "15",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_MARGIN_RIGHT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_MARGIN_RIGHT"),
			'SORT' => 3500,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "15",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_MARGIN_BOTTOM" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_MARGIN_BOTTOM"),
			'SORT' => 3600,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "15",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_MARGIN_LEFT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_MARGIN_LEFT"),
			'SORT' => 3700,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "20",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_NUMBER_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_NUMBER_VALUE", null, 'de').'"',
			'SORT' => 3800,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_NUMBER_VALUE", null, 'de'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_NUMBER_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SORT"),
			'SORT' => 3850,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 100,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_NUMBER_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SHOW"),
			'SORT' => 3900,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLDE_COLUMN_NAME_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_NAME_VALUE", null, 'de').'"',
			'SORT' => 4000,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_NAME_VALUE", null, 'de'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_NAME_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SORT"),
			'SORT' => 4050,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 200,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_NAME_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SHOW"),
			'SORT' => 4100,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLDE_COLUMN_QUANTITY_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_QUANTITY_VALUE", null, 'de').'"',
			'SORT' => 4200,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_QUANTITY_VALUE", null, 'de'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_QUANTITY_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SORT"),
			'SORT' => 4200,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 300,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_QUANTITY_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SHOW"),
			'SORT' => 4250,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLDE_COLUMN_MEASURE_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_MEASURE_VALUE", null, 'de').'"',
			'SORT' => 4300,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_DE_COLUMN_MEASURE_VALUE', null, 'de'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_MEASURE_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SORT"),
			'SORT' => 4400,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 400,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_MEASURE_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SHOW"),
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
		"BILLDE_COLUMN_PRICE_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_PRICE_VALUE", null, 'de').'"',
			'SORT' => 4600,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_PRICE_VALUE", null, 'de'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_PRICE_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SORT"),
			'SORT' => 4650,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 500,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_PRICE_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SHOW"),
			'SORT' => 4700,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLDE_COLUMN_VAT_RATE_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_VAT_RATE_VALUE", null, 'de').'"',
			'SORT' => 4800,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_DE_COLUMN_VAT_RATE_VALUE', null, 'de'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_VAT_RATE_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SORT"),
			'SORT' => 4900,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 600,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_VAT_RATE_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SHOW"),
			'SORT' => 5000,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLDE_COLUMN_SUM_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SUM_VALUE", null, 'de').'"',
			'SORT' => 5100,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SUM_VALUE", null, 'de'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_SUM_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SORT"),
			'SORT' => 5150,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 700,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COLUMN_SUM_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COLUMN_SHOW"),
			'SORT' => 5200,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		)
	)
);