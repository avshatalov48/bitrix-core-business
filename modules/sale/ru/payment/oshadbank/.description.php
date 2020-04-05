<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/oshadbank.php"));

$psTitle = GetMessage("SIBP_DTITLE");
$psDescription = GetMessage("SIBP_TITLE_DESC");

$arPSCorrespondence = array(
		"RECIPIENT_NAME" => array(
				"NAME" => GetMessage("SIBP_RECIPIENT_NAME"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"RECIPIENT_ID" => array(
				"NAME" => GetMessage("SIBP_RECIPIENT_ID"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"RECIPIENT_NUMBER" => array(
				"NAME" => GetMessage("SIBP_RECIPIENT_NUMBER"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"RECIPIENT_BANK" => array(
				"NAME" => GetMessage("SIBP_RECIPIENT_BANK"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"RECIPIENT_CODE_BANK" => array(
				"NAME" => GetMessage("SIBP_RECIPIENT_CODE_BANK"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYER_FIO" => array(
				"NAME" => GetMessage("SIBP_PAYER_FIO"),
				"DESCR" => "",
				"VALUE" => "FIO",
				"TYPE" => "PROPERTY"
			),
		"PAYER_INDEX" => array(
				"NAME" => GetMessage("SIBP_INDEX"),
				"DESCR" => "",
				"VALUE" => "ZIP",
				"TYPE" => "PROPERTY"
			),
		"PAYER_TOWN" => array(
				"NAME" => GetMessage("SIBP_TOWN"),
				"DESCR" => "",
				"VALUE" => "LOCATION_CITY",
				"TYPE" => "PROPERTY"
			),
		"PAYER_ADRES" => array(
				"NAME" => GetMessage("SIBP_PAYER_ADRES"),
				"DESCR" => "",
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			),
		"PAYER_NUMBER" => array(
				"NAME" => GetMessage("SIBP_PAYER_NUMBER"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYMENT_PERIOD" => array(
				"NAME" => GetMessage("SIBP_PAYMENT_PERIOD"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYMENT_CODE" => array(
				"NAME" => GetMessage("SIBP_PAYMENT_CODE"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYMENT_CLASSIC" => array(
				"NAME" => GetMessage("SIBP_PAYMENT_CLASSIC"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"ORDER_ID" => array(
				"NAME" => GetMessage("SIBP_ORDER_ID"),
				"DESCR" => "",
				"VALUE" => "ID",
				"TYPE" => "ORDER"
			),
		"ORDER_DATE" => array(
				"NAME" => GetMessage("SIBP_ORDER_DATE"),
				"DESCR" => "",
				"VALUE" => "DATE_INSERT",
				"TYPE" => "ORDER"
			),
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("SIBP_SUMMA"),
				"DESCR" => "",
				"VALUE" => "SHOULD_PAY",
				"TYPE" => "ORDER"
			),

	);
?>