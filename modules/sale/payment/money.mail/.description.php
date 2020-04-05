<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/money_mail.php"));

$psTitle = GetMessage("MM_TITLE");
$psDescription = GetMessage("MM_DESCRIPTION");
$isAvailable = \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_FALSE;

$arPSCorrespondence = array(
		"KEY" => array(
				"NAME" =>GetMessage("MM_KEY"),
				"DESCR" => GetMessage("MM_KEY_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"BUYER_EMAIL" => array(
				"NAME" => GetMessage("MM_EMAIL"),
				"DESCR" => GetMessage("MM_EMAIL_DESC"),
				"VALUE" => "EMAIL",
				"TYPE" => "PROPERTY"
			),
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("MM_SHOULD_PAY"),
				"DESCR" => GetMessage("MM_SHOULD_PAY_DESC"),
				"VALUE" => "SHOULD_PAY",
				"TYPE" => "ORDER"
			),
		//"CURRENCY" => array(
		//		"NAME" => GetMessage("MM_CURRENCY"),
		//		"DESCR" => GetMessage("MM_CURRENCY_DESC"),
		//		"VALUE" => "CURRENCY",
		//		"TYPE" => "ORDER"
		//	),
		"ORDER_ID" => array(
				"NAME" => GetMessage("MM_ORDER_ID"),
				"DESCR" => "",
				"VALUE" => "ID",
				"TYPE" => "ORDER"
			),
		"DATE_INSERT" => array(
				"NAME" => GetMessage("MM_DATE_INSERT"),
				"DESCR" => GetMessage("MM_DATE_INSERT_DESC"),
				"VALUE" => "DATE_INSERT",
				"TYPE" => "ORDER"
			),
		"TEST_MODE" => array(
				"NAME" => GetMessage("MM_TEST"),
				"DESCR" => GetMessage("MM_TEST_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
);
?>
