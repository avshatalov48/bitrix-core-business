<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));

$psTitle = GetMessage("SPCP_DTITLE");
$psDescription = GetMessage("SPCP_DDESCR");

$arPSCorrespondence = array(
		"AcquirerBin" => array(
				"NAME" => GetMessage("AcquirerBin"),
				"DESCR" => GetMessage("AcquirerBin_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"MerchantName" => array(
				"NAME" => GetMessage("MerchantName"),
				"DESCR" => GetMessage("MerchantName_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"MerchantURL" => array(
				"NAME" => GetMessage("MerchantURL"),
				"DESCR" => GetMessage("MerchantURL_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"MerchantCity" => array(
				"NAME" => GetMessage("MerchantCity"),
				"DESCR" => GetMessage("MerchantCity_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"MerchantID" => array(
				"NAME" => GetMessage("MerchantID"),
				"DESCR" => GetMessage("MerchantID_DESCR"),
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