<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/.description.php"));

$psTitle = "RBK Money";
$psDescription = "<a href=\"http://www.rbkmoney.ru\" target=\"_blank\">http://www.rbkmoney.ru</a>";
$arPSCorrespondence = array(
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("SALE_RBK_SHOULD_PAY"),
				"DESCR" => GetMessage("SALE_RBK_DESC_SHOULD_PAY"),
				"VALUE" => "SHOULD_PAY",
				"TYPE" => "ORDER"
			),
		"CURRENCY" => array(
				"NAME" => GetMessage("SALE_RBK_CURRENCY"),
				"DESCR" => GetMessage("SALE_RBK_DESC_CURRENCY"),
				"VALUE" => "CURRENCY",
				"TYPE" => "ORDER"
			),
		"ESHOP_ID" => array(
				"NAME" => GetMessage("SALE_RBK_ESHOP_ID"),
				"DESCR" => GetMessage("SALE_RBK_DESC_ESHOP_ID"),
				"VALUE" => "0000000",
				"TYPE" => ""
			),
		"SERVICE_NAME" => array(
				"NAME" => GetMessage("SALE_RBK_SERVICE_NAME"),
				"DESCR" => GetMessage("SALE_RBK_DESC_SERVICE_NAME"),
				"VALUE" => "Order ",
				"TYPE" => ""
			),
		"ORDER_ID" => array(
				"NAME" => GetMessage("SALE_RBK_ORDER_ID"),
				"DESCR" => GetMessage("SALE_RBK_DESC_ORDER_ID"),
				"VALUE" => "ID",
				"TYPE" => "ORDER"
			),
		"SUCCESS_URL" => array(
				"NAME" => GetMessage("SALE_RBK_SUCCESS_URL"),
				"DESCR" => GetMessage("SALE_RBK_DESC_SUCCESS_URL"),
				"VALUE" => "http://www.yoursite.com/sale/payment_result.php",
				"TYPE" => ""
			),
		"FAIL_URL" => array(
				"NAME" => GetMessage("SALE_RBK_FAIL_URL"),
				"DESCR" => GetMessage("SALE_RBK_DESC_FAIL_URL"),
				"VALUE" => "http://www.yoursite.com/sale/payment_failed.php",
				"TYPE" => ""
			),
		"SECRET_KEY" => array(
				"NAME" => GetMessage("SALE_RBK_SECRET_KEY"),
				"DESCR" => GetMessage("SALE_RBK_DESC_SECRET_KEY"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAY_BUTTON" => array(
				"NAME" => GetMessage("SALE_RBK_PAY_BUTTON"),
				"DESCR" => GetMessage("SALE_RBK_DESC_PAY_BUTTON"),
				"VALUE" => "Pay!",
				"TYPE" => ""
			),
		"USER_FIELD_1" => array(
				"NAME" => GetMessage("SALE_RBK_USER_FIELD_1"),
				"DESCR" => GetMessage("SALE_RBK_DESC_USER_FIELD_1"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"USER_FIELD_2" => array(
				"NAME" => GetMessage("SALE_RBK_USER_FIELD_2"),
				"DESCR" => GetMessage("SALE_RBK_DESC_USER_FIELD_2"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"USER_FIELD_3" => array(
				"NAME" => GetMessage("SALE_RBK_USER_FIELD_3"),
				"DESCR" => GetMessage("SALE_RBK_DESC_USER_FIELD_3"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"F_NAME" => array(
				"NAME" => GetMessage("SALE_RBK_F_NAME"),
				"DESCR" => GetMessage("SALE_RBK_DESC_F_NAME"),
				"VALUE" => "NAME",
				"TYPE" => "USER"
			),
		"S_NAME" => array(
				"NAME" => GetMessage("SALE_RBK_S_NAME"),
				"DESCR" => GetMessage("SALE_RBK_DESC_S_NAME"),
				"VALUE" => "LAST_NAME",
				"TYPE" => "USER"
			),
		"EMAIL" => array(
				"NAME" => GetMessage("SALE_RBK_EMAIL"),
				"DESCR" => GetMessage("SALE_RBK_DESC_EMAIL"),
				"VALUE" => "EMAIL",
				"TYPE" => "USER"
			),
		"PAYMENT_VALUE" => array(
				"NAME" => GetMessage("SALE_RBK_ACTION_TYPE"),
				"DESCR" => '',
				"TYPE" => "SELECT",
				"VALUE" => array(
					"" => array(
						"NAME" => GetMessage("SALE_PAY_ALL"),
					),
					"inner" => array(
						"NAME" => GetMessage("SALE_PAY_RBK"),
					),
					"bankCard" => array(
							"NAME" => GetMessage("SALE_PAY_VISA"),
						),
					"exchangers" => array(
							"NAME" => GetMessage("SALE_PAY_ECOM"),
						),
					"transfers" => array(
							"NAME" => GetMessage("SALE_PAY_TRANSFER"),
						),
					"terminals" => array(
							"NAME" => GetMessage("SALE_PAY_TERMINAL"),
						),
					"sberbank" => array(
							"NAME" => GetMessage("SALE_PAY_BANK"),
						),
					"postRus" => array(
							"NAME" => GetMessage("SALE_PAY_POST"),
						),
					"ibank" => array(
							"NAME" => GetMessage("SALE_PAY_INET"),
						),
					"svyaznoy" => array(
							"NAME" => GetMessage("SALE_PAY_SVIAZ"),
						),
					"euroset" => array(
							"NAME" => GetMessage("SALE_PAY_EURO"),
						),
					"mts" => array(
							"NAME" => GetMessage("SALE_PAY_MTC"),
						),
					"uralsib" => array(
							"NAME" => GetMessage("SALE_PAY_URAL"),
						),
					"handybank" => array(
							"NAME" => GetMessage("SALE_PAY_HANDY"),
						),
					"ocean" => array(
							"NAME" => GetMessage("SALE_PAY_OCEAN"),
						),
					"ibankuralsib" => array(
							"NAME" => GetMessage("SALE_PAY_URALSIB"),
						),
			),
		),
		"CHANGE_STATUS_PAY" => array(
				"NAME" => GetMessage("PYM_CHANGE_STATUS_PAY"),
				"DESCR" => GetMessage("PYM_CHANGE_STATUS_PAY_DESC"),
				"VALUE" => "Y",
				"TYPE" => ""
			),
	);
?>