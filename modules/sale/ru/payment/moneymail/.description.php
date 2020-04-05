<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));

$psTitle = GetMessage("SPCP_DTITLE");
$psDescription = GetMessage("SPCP_DDESCR");

$arPSCorrespondence = array(
		"ShopEmail" => array(
				"NAME" => GetMessage("ShopEmail"),
				"DESCR" => GetMessage("ShopEmail_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"PASS" => array(
				"NAME" => GetMessage("PASS"),
				"DESCR" => GetMessage("PASS_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYER_EMAIL" => array(
				"NAME" => GetMessage("PAYER_EMAIL"),
				"DESCR" => GetMessage("PAYER_EMAIL_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"ERROR_URL" => array(
				"NAME" => GetMessage("ERROR_URL"),
				"DESCR" => GetMessage("ERROR_URL_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("SHOULD_PAY"),
				"DESCR" => GetMessage("SHOULD_PAY_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"CURRENCY" => array(
				"NAME" => GetMessage("CURRENCY"),
				"DESCR" => GetMessage("CURRENCY_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"DATE_INSERT" => array(
				"NAME" => GetMessage("DATE_INSERT"),
				"DESCR" => GetMessage("DATE_INSERT_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
	);
?>