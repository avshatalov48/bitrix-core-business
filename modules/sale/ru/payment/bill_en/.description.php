<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$langFile = GetLangFileName(dirname(__FILE__)."/", "/bill.php");

if(file_exists($langFile))
	include($langFile);


$psTitle = GetMessage("SBLP_DTITLE");
$psDescription = GetMessage("SBLP_DDESCR");

$arPSCorrespondence = array(
		"DATE_INSERT" => array(
				"NAME" => GetMessage("SBLP_DATE"),
				"DESCR" => GetMessage("SBLP_DATE_DESC"),
				"VALUE" => "DATE_INSERT",
				"TYPE" => "ORDER"
			),

		"DATE_PAY_BEFORE" => array(
				"NAME" => GetMessage("SBLP_PAY_BEFORE"),
				"DESCR" => GetMessage("SBLP_PAY_BEFORE_DESC"),
				"VALUE" => "DATE_PAY_BEFORE",
				"TYPE" => "ORDER"
			),
		"SELLER_NAME" => array(
				"NAME" => GetMessage("SBLP_SUPPLI"),
				"DESCR" => GetMessage("SBLP_SUPPLI_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_ADDRESS" => array(
				"NAME" => GetMessage("SBLP_ADRESS_SUPPLI"),
				"DESCR" => GetMessage("SBLP_ADRESS_SUPPLI_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_PHONE" => array(
				"NAME" => GetMessage("SBLP_PHONE_SUPPLI"),
				"DESCR" => GetMessage("SBLP_PHONE_SUPPLI_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),

		"SELLER_BANK_ACCNO" => array(
				"NAME" => GetMessage("SBLP_BANK_ACCNO_SUPPLI"),
				"DESCR" => GetMessage("SBLP_BANK_ACCNO_SUPPLI_DESC"),
				"VALUE" => GetMessage("SBLP_BANK_ACCNO_SUPPLI_VAL"),
				"TYPE" => ""
			),
		"SELLER_BANK" => array(
				"NAME" => GetMessage("SBLP_BANK_SUPPLI"),
				"DESCR" => GetMessage("SBLP_BANK_SUPPLI_DESC"),
				"VALUE" => GetMessage("SBLP_BANK_SUPPLI_VAL"),
				"TYPE" => ""
			),
		"SELLER_BANK_ADDR" => array(
				"NAME" => GetMessage("SBLP_BANK_ADDR_SUPPLI"),
				"DESCR" => GetMessage("SBLP_BANK_ADDR_SUPPLI_DESC"),
				"VALUE" => GetMessage("SBLP_BANK_ADDR_SUPPLI_VAL"),
				"TYPE" => ""
			),
		"SELLER_BANK_PHONE" => array(
				"NAME" => GetMessage("SBLP_BANK_PHONE_SUPPLI"),
				"DESCR" => GetMessage("SBLP_BANK_PHONE_SUPPLI_DESC"),
				"VALUE" => GetMessage("SBLP_BANK_PHONE_SUPPLI_VAL"),
				"TYPE" => ""
			),
		"SELLER_BANK_ROUTENO" => array(
				"NAME" => GetMessage("SBLP_BANK_ROUTENO_SUPPLI"),
				"DESCR" => GetMessage("SBLP_BANK_ROUTENO_SUPPLI_DESC"),
				"VALUE" => GetMessage("SBLP_BANK_ROUTENO_SUPPLI_VAL"),
				"TYPE" => ""
			),
		"SELLER_BANK_SWIFT" => array(
				"NAME" => GetMessage("SBLP_BANK_SWIFT_SUPPLI"),
				"DESCR" => GetMessage("SBLP_BANK_SWIFT_SUPPLI_DESC"),
				"VALUE" => GetMessage("SBLP_BANK_SWIFT_SUPPLI_VAL"),
				"TYPE" => ""
			),
		"SELLER_DIR" => array(
				"NAME" => GetMessage("SBLP_DIR_SUPPLI"),
				"DESCR" => GetMessage("SBLP_DIR_SUPPLI_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_ACC" => array(
				"NAME" => GetMessage("SBLP_ACC_SUPPLI"),
				"DESCR" => GetMessage("SBLP_ACC_SUPPLI_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),

		"BUYER_NAME" => array(
				"NAME" => GetMessage("SBLP_CUSTOMER"),
				"DESCR" => GetMessage("SBLP_CUSTOMER_DESC"),
				"VALUE" => "COMPANY_NAME",
				"TYPE" => "PROPERTY"
			),
		"BUYER_ADDRESS" => array(
				"NAME" => GetMessage("SBLP_CUSTOMER_ADRES"),
				"DESCR" => GetMessage("SBLP_CUSTOMER_ADRES_DESC"),
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			),
		"BUYER_PHONE" => array(
				"NAME" => GetMessage("SBLP_CUSTOMER_PHONE"),
				"DESCR" => GetMessage("SBLP_CUSTOMER_PHONE_DESC"),
				"VALUE" => "PHONE",
				"TYPE" => "PROPERTY"
			),
		"BUYER_FAX" => array(
				"NAME" => GetMessage("SBLP_CUSTOMER_FAX"),
				"DESCR" => GetMessage("SBLP_CUSTOMER_FAX_DESC"),
				"VALUE" => "FAX",
				"TYPE" => "PROPERTY"
			),
		"BUYER_PAYER_NAME" => array(
				"NAME" => GetMessage("SBLP_CUSTOMER_PERSON"),
				"DESCR" => GetMessage("SBLP_CUSTOMER_PERSON_DESC"),
				"VALUE" => "PAYER_NAME",
				"TYPE" => "PROPERTY"
			),
		"COMMENT1" => array(
				"NAME" => GetMessage("SBLP_COMMENT1"),
				"DESCR" => "",
				"VALUE" => GetMessage("SBLP_COMMENT1_VALUE"),
				"TYPE" => ""
			),
		"COMMENT2" => array(
				"NAME" => GetMessage("SBLP_COMMENT2"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"PATH_TO_LOGO" => array(
				"NAME" => GetMessage("SBLP_LOGO"),
				"DESCR" => GetMessage("SBLP_LOGO_DESC"),
				"VALUE" => "",
				"TYPE" => "FILE"
			),
		"PATH_TO_STAMP" => array(
				"NAME" => GetMessage("SBLP_PRINT"),
				"DESCR" => GetMessage("SBLP_PRINT_DESC"),
				"VALUE" => "",
				"TYPE" => "FILE"
			),
		"SELLER_DIR_SIGN" => array(
				"NAME" => GetMessage("SBLP_DIR_SIGN_SUPPLI"),
				"DESCR" => GetMessage("SBLP_DIR_SIGN_SUPPLI_DESC"),
				"VALUE" => "",
				"TYPE" => "FILE"
			),
		"SELLER_ACC_SIGN" => array(
				"NAME" => GetMessage("SBLP_ACC_SIGN_SUPPLI"),
				"DESCR" => GetMessage("SBLP_ACC_SIGN_SUPPLI_DESC"),
				"VALUE" => "",
				"TYPE" => "FILE"
			),
		"BACKGROUND" => array(
				"NAME" => GetMessage("SBLP_BACKGROUND"),
				"DESCR" => GetMessage("SBLP_BACKGROUND_DESC"),
				"VALUE" => "",
				"TYPE" => "FILE"
			),
		"BACKGROUND_STYLE" => array(
				"NAME" => GetMessage("SBLP_BACKGROUND_STYLE"),
				"DESCR" => "",
				"VALUE" => array(
					'none' => array('NAME' => GetMessage("SBLP_BACKGROUND_STYLE_NONE")),
					'tile' => array('NAME' => GetMessage("SBLP_BACKGROUND_STYLE_TILE")),
					'stretch' => array('NAME' => GetMessage("SBLP_BACKGROUND_STYLE_STRETCH"))
				),
				"TYPE" => "SELECT"
			),
		"MARGIN_TOP" => array(
				"NAME" => GetMessage("SBLP_MARGIN_TOP"),
				"DESCR" => "",
				"VALUE" => "15",
				"TYPE" => ""
			),
		"MARGIN_RIGHT" => array(
				"NAME" => GetMessage("SBLP_MARGIN_RIGHT"),
				"DESCR" => "",
				"VALUE" => "15",
				"TYPE" => ""
			),
		"MARGIN_BOTTOM" => array(
				"NAME" => GetMessage("SBLP_MARGIN_BOTTOM"),
				"DESCR" => "",
				"VALUE" => "15",
				"TYPE" => ""
			),
		"MARGIN_LEFT" => array(
				"NAME" => GetMessage("SBLP_MARGIN_LEFT"),
				"DESCR" => "",
				"VALUE" => "20",
				"TYPE" => ""
			)
	);
?>