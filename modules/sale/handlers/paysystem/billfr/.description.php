<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
Loc::loadLanguageFile(__FILE__, 'fr');

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_TITLE'),
	'SORT' => 1500,
	'CODES' => array(
		'DATE_INSERT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_DATE'),
			'SORT' => 100,
			'GROUP' => 'PAYMENT',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_DATE_DESC'),
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'DATE_BILL_DATE',
				'PROVIDER_KEY' => 'PAYMENT'
			)
		),
		'DATE_PAY_BEFORE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_PAY_BEFORE'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_PAY_BEFORE_DESC'),
			'SORT' => 200,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'DATE_PAY_BEFORE',
				'PROVIDER_KEY' => 'ORDER'
			)
		),
		'SELLER_COMPANY_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_SUPPLI'),
			'GROUP' => 'SELLER_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_SUPPLI_DESC'),
			'SORT' => 300
		),
		'SELLER_COMPANY_ADDRESS' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_ADRESS_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_ADRESS_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 400
		),
		'SELLER_COMPANY_PHONE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_PHONE_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_PHONE_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 500
		),
		'SELLER_COMPANY_BANK_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 600,
		),
		'SELLER_COMPANY_BANK_ACCOUNT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_ACCNO_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_ACCNO_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 700,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_ACCNO_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_BANK_ADDR' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_ADDR_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_ADDR_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 800,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_ADDR_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_BANK_PHONE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_PHONE_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_PHONE_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 900,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_PHONE_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_BANK_ACCOUNT_CORR' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_ROUTENO_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_ROUTENO_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1000,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_ROUTENO_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_BANK_SWIFT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_SWIFT_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_SWIFT_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1100,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_FR_BANK_SWIFT_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_DIRECTOR_POSITION' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_DIR_POS_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_DIR_POS_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1200,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_FR_DIR_POS_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_ACCOUNTANT_POSITION' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_ACC_POS_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_ACC_POS_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1300,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_FR_ACC_POS_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_DIRECTOR_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_DIR_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_DIR_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1400
		),
		'SELLER_COMPANY_ACCOUNTANT_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_ACC_SUPPLI'),
			'GROUP' => 'SELLER_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_ACC_SUPPLI_DESC')
		),
		"BILLFR_PAYER_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_PAYER_SHOW"),
			'SORT' => 1410,
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		'BUYER_PERSON_COMPANY_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_CUSTOMER'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_CUSTOMER_DESC'),
			'SORT' => 1500,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'COMPANY_NAME',
				'PROVIDER_KEY' => 'PROPERTY'
			)
		),
		'BUYER_PERSON_COMPANY_ADDRESS' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_CUSTOMER_ADRES'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_CUSTOMER_ADRES_DESC'),
			'SORT' => 1600,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'ADDRESS',
				'PROVIDER_KEY' => 'PROPERTY'
			)
		),
		'BUYER_PERSON_COMPANY_NAME_CONTACT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_CUSTOMER_PERSON'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_CUSTOMER_PERSON_DESC'),
			'SORT' => 1900,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'PAYER_NAME',
				'PROVIDER_KEY' => 'PROPERTY'
			)
		),
		"BILLFR_HEADER" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_HEADER"),
			'SORT' => 1920,
			'GROUP' => 'GENERAL_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_FR_HEADER_VALUE', null, 'fr'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_TOTAL_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_TOTAL_SHOW"),
			'SORT' => 1940,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		'BILLFR_COMMENT1' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_COMMENT1'),
			'GROUP' => 'GENERAL_SETTINGS',
			'SORT' => 2000,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_FR_COMMENT1_VALUE'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'BILLFR_COMMENT2' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_COMMENT2'),
			'GROUP' => 'GENERAL_SETTINGS',
			'SORT' => 2100,
		),
		'BILLFR_PATH_TO_LOGO' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_LOGO'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_LOGO_DESC'),
			'SORT' => 2200,
			'GROUP' => 'SELLER_COMPANY',
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		'BILLFR_LOGO_DPI' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_LOGO_DPI'),
			'SORT' => 2300,
			'GROUP' => 'SELLER_COMPANY',
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'96' => Loc::getMessage('SALE_HPS_BILL_FR_LOGO_DPI_96'),
					'600' => Loc::getMessage('SALE_HPS_BILL_FR_LOGO_DPI_600'),
					'300' => Loc::getMessage('SALE_HPS_BILL_FR_LOGO_DPI_300'),
					'150' => Loc::getMessage('SALE_HPS_BILL_FR_LOGO_DPI_150'),
					'72' => Loc::getMessage('SALE_HPS_BILL_FR_LOGO_DPI_72')
				)
			)
		),
		'BILLFR_PATH_TO_STAMP' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_PRINT'),
			'SORT' => 2400,
			'GROUP' => 'SELLER_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_PRINT_DESC'),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		'SELLER_COMPANY_DIR_SIGN' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_DIR_SIGN_SUPPLI'),
			'SORT' => 2500,
			'GROUP' => 'SELLER_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_DIR_SIGN_SUPPLI_DESC'),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		'SELLER_COMPANY_ACC_SIGN' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_ACC_SIGN_SUPPLI'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 2600,
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_ACC_SIGN_SUPPLI_DESC'),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		'BILLFR_BACKGROUND' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_BACKGROUND'),
			'SORT' => 2700,
			'GROUP' => 'VISUAL_SETTINGS',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_FR_BACKGROUND_DESC'),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		'BILLFR_BACKGROUND_STYLE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_BACKGROUND_STYLE'),
			'SORT' => 2800,
			'TYPE' => 'SELECT',
			'GROUP' => 'VISUAL_SETTINGS',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'tile' => Loc::getMessage('SALE_HPS_BILL_FR_BACKGROUND_STYLE_TILE'),
					'stretch' => Loc::getMessage('SALE_HPS_BILL_FR_BACKGROUND_STYLE_STRETCH')
				)
			)
		),
		'BILLFR_MARGIN_TOP' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_MARGIN_TOP'),
			'SORT' => 2900,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => '15',
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'BILLFR_MARGIN_RIGHT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_MARGIN_RIGHT'),
			'SORT' => 3000,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => '15',
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'BILLFR_MARGIN_BOTTOM' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_MARGIN_BOTTOM'),
			'SORT' => 3100,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => '15',
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'BILLFR_MARGIN_LEFT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_FR_MARGIN_LEFT'),
			'SORT' => 3200,
			'GROUP' => 'VISUAL_SETTINGS',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => '20',
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		"BILLFR_COLUMN_NUMBER_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_NUMBER_VALUE", null, 'fr').'"',
			'SORT' => 3300,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_NUMBER_VALUE", null, 'fr'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_NUMBER_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SORT"),
			'SORT' => 3350,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 100,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_NUMBER_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SHOW"),
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
		"BILLFR_COLUMN_NAME_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_NAME_VALUE", null, 'fr').'"',
			'SORT' => 3500,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_NAME_VALUE", null, 'fr'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_NAME_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SORT"),
			'SORT' => 3550,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 200,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_NAME_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SHOW"),
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
		"BILLFR_COLUMN_QUANTITY_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_QUANTITY_VALUE", null, 'fr').'"',
			'SORT' => 3700,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_QUANTITY_VALUE", null, 'fr'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_QUANTITY_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SORT"),
			'SORT' => 3750,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 300,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_QUANTITY_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SHOW"),
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
		"BILLFR_COLUMN_MEASURE_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_MEASURE_VALUE", null, 'fr').'"',
			'SORT' => 3900,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_FR_COLUMN_MEASURE_VALUE', null, 'fr'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_MEASURE_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SORT"),
			'SORT' => 3950,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 400,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_MEASURE_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SHOW"),
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
		"BILLFR_COLUMN_PRICE_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_PRICE_VALUE", null, 'fr').'"',
			'SORT' => 4100,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_PRICE_VALUE", null, 'fr'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_PRICE_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SORT"),
			'SORT' => 4150,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 500,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_PRICE_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SHOW"),
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
		"BILLFR_COLUMN_VAT_RATE_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_VAT_RATE_VALUE", null, 'fr').'"',
			'SORT' => 4300,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage('SALE_HPS_BILL_FR_COLUMN_VAT_RATE_VALUE', null, 'fr'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_VAT_RATE_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SORT"),
			'SORT' => 4350,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 600,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_VAT_RATE_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SHOW"),
			'SORT' => 4400,
			'GROUP' => 'COLUMN_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "Y",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"BILLFR_COLUMN_SUM_TITLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_TITLE").'"'.Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SUM_VALUE", null, 'fr').'"',
			'SORT' => 4500,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SUM_VALUE", null, 'fr'),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_SUM_SORT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SORT"),
			'SORT' => 4550,
			'GROUP' => 'COLUMN_SETTINGS',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => 700,
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLFR_COLUMN_SUM_SHOW" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_FR_COLUMN_SHOW"),
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
	)
);