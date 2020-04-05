<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/.description.php"));

$psTitle = "ChronoPay";
$psDescription = "<a href=\"http://www.chronopay.com\" target=\"_blank\">http://www.chronopay.com</a>";

$arPSCorrespondence = array(
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("CHRP_SHOULD_PAY"),
				"DESCR" => GetMessage("CHRP_DESC_SHOULD_PAY"),
				"VALUE" => "SHOULD_PAY",
				"TYPE" => "ORDER"
			),
		"PRODUCT_ID" => array(
				"NAME" => GetMessage("CHRP_PRODUCT_ID"),
				"DESCR" => GetMessage("CHRP_DESC_PRODUCT_ID"),
				"VALUE" => "000000-0000-0000",
				"TYPE" => ""
			),
		"PRODUCT_NAME" => array(
				"NAME" => GetMessage("CHRP_PRODUCT_NAME"),
				"DESCR" => GetMessage("CHRP_DESC_PRODUCT_NAME"),
				"VALUE" => "Order ",
				"TYPE" => ""
			),
		"ORDER_ID" => array(
				"NAME" => GetMessage("CHRP_ORDER_ID"),
				"DESCR" => GetMessage("CHRP_DESC_ORDER_ID"),
				"VALUE" => "ID",
				"TYPE" => "ORDER"
			),
		"CB_URL" => array(
				"NAME" => GetMessage("CHRP_CB_URL"),
				"DESCR" => GetMessage("CHRP_DESC_CB_URL"),
				"VALUE" => "http://www.yoursite.com/sale/payment_result.php",
				"TYPE" => ""
			),
		"SUCCESS_URL" => array(
				"NAME" => GetMessage("CHRP_SUCCESS_URL"),
				"DESCR" => GetMessage("CHRP_DESC_SUCCESS_URL"),
				"VALUE" => "http://www.yoursite.com/sale/payment_success.php",
				"TYPE" => ""
			),
		"DECLINE_URL" => array(
				"NAME" => GetMessage("CHRP_DECLINE_URL"),
				"DESCR" => GetMessage("CHRP_DESC_DECLINE_URL"),
				"VALUE" => "http://www.yoursite.com/sale/payment_failed.php",
				"TYPE" => ""
			),
		"SHARED" => array(
				"NAME" => GetMessage("CHRP_SHARED"),
				"DESCR" => GetMessage("CHRP_DESC_SHARED"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"YANDEX_FORWARD" => array(
				"NAME" => GetMessage("CHRP_YANDEX_FORWARD"),
				"DESCR" => GetMessage("CHRP_DESC_YANDEX_FORWARD"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"WEBMONEY_FORWARD" => array(
				"NAME" => GetMessage("CHRP_WEBMONEY_FORWARD"),
				"DESCR" => GetMessage("CHRP_DESC_WEBMONEY_FORWARD"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"QIWI_FORWARD" => array(
				"NAME" => GetMessage("CHRP_QIWI_FORWARD"),
				"DESCR" => GetMessage("CHRP_DESC_QIWI_FORWARD"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAY_BUTTON" => array(
				"NAME" => GetMessage("CHRP_PAY_BUTTON"),
				"DESCR" => GetMessage("CHRP_DESC_PAY_BUTTON"),
				"VALUE" => "Pay!",
				"TYPE" => ""
			),
		"CS2" => array(
				"NAME" => GetMessage("CHRP_CS2"),
				"DESCR" => GetMessage("CHRP_DESC_CS2"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"CS3" => array(
				"NAME" => GetMessage("CHRP_CS3"),
				"DESCR" => GetMessage("CHRP_DESC_CS3"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"LANGUAGE" => array(
				"NAME" => GetMessage("CHRP_LANGUAGE"),
				"DESCR" => GetMessage("CHRP_DESC_LANGUAGE"),
				"VALUE" => "En",
				"TYPE" => ""
			),
		"F_NAME" => array(
				"NAME" => GetMessage("CHRP_F_NAME"),
				"DESCR" => GetMessage("CHRP_DESC_F_NAME"),
				"VALUE" => "NAME",
				"TYPE" => "USER"
			),
		"S_NAME" => array(
				"NAME" => GetMessage("CHRP_S_NAME"),
				"DESCR" => GetMessage("CHRP_DESC_S_NAME"),
				"VALUE" => "LAST_NAME",
				"TYPE" => "USER"
			),
		"EMAIL" => array(
				"NAME" => GetMessage("CHRP_EMAIL"),
				"DESCR" => GetMessage("CHRP_DESC_EMAIL"),
				"VALUE" => "EMAIL",
				"TYPE" => "USER"
			),
		"STREET" => array(
				"NAME" => GetMessage("CHRP_STREET"),
				"DESCR" => GetMessage("CHRP_DESC_STREET"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"CITY" => array(
				"NAME" => GetMessage("CHRP_CITY"),
				"DESCR" => GetMessage("CHRP_DESC_CITY"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"STATE" => array(
				"NAME" => GetMessage("CHRP_STATE"),
				"DESCR" => GetMessage("CHRP_DESC_STATE"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"ZIP" => array(
				"NAME" => GetMessage("CHRP_ZIP"),
				"DESCR" => GetMessage("CHRP_DESC_ZIP"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"COUNTRY" => array(
				"NAME" => GetMessage("CHRP_COUNTRY"),
				"DESCR" => GetMessage("CHRP_DESC_COUNTRY"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"PHONE" => array(
				"NAME" => GetMessage("CHRP_PHONE"),
				"DESCR" => GetMessage("CHRP_DESC_PHONE"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"ORDER_UNIQ" => array(
				"NAME" => GetMessage("CHRP_ORDER_UNIQ"),
				"DESCR" => GetMessage("CHRP_DESC_ORDER_UNIQ"),
				"VALUE" => "",
				"TYPE" => ""
			),
	);
?>