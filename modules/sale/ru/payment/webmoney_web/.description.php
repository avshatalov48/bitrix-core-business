<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

include \Bitrix\Main\Application::getDocumentRoot().'/bitrix/modules/sale/handlers/paysystem/webmoney/.description.php';

return;
include(GetLangFileName(dirname(__FILE__)."/", "/webmoney_web.php"));

$psTitle = GetMessage("SWMWP_DTITLE");
$psDescription = GetMessage("SWMWP_DDESCR");

$arPSCorrespondence = array(
		"SHOP_ACCT" => array(
				"NAME" => GetMessage("SWMWP_NUMBER"),
				"DESCR" => GetMessage("SWMWP_NUMBER_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"TEST_MODE" => array(
				"NAME" => GetMessage("SWMWP_TEST"),
				"DESCR" => GetMessage("SWMWP_TEST_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"CNST_SECRET_KEY" => array(
				"NAME" => GetMessage("SWMWP_KEY"),
				"DESCR" => GetMessage("SWMWP_KEY_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"HASH_ALGO" => array(
				"NAME" => GetMessage("SWMWP_HASH_ALGO"),
				"DESCR" => GetMessage("SWMWP_HASH_ALGO_DESC"),
				"VALUE" => "md5",
				"TYPE" => ""
			),
		"ORDER_ID" => Array(
				"NAME" => GetMessage("SWMWP_ORDER_ID"),
				"VALUE" => "ID",
				"TYPE" => "ORDER",
			),
		"DATE_INSERT" => Array(
				"NAME" => GetMessage("SWMWP_DATE"),
				"VALUE" => "DATE_INSERT_DATE",
				"TYPE" => "ORDER",
			),
		"SHOULD_PAY" => Array(
				"NAME" => GetMessage("SWMWP_SUMMA"),
				"DESCR" => "",
				"VALUE" => "SHOULD_PAY",
				"TYPE" => "ORDER",
			),
		"RESULT_URL" => Array(
				"NAME" => GetMessage("SWMWP_URL"),
				"DESCR" => GetMessage("SWMWP_URL_DESC"),
				"VALUE" => "",
				"TYPE" => "",
			),
		"SUCCESS_URL" => Array(
				"NAME" => GetMessage("SWMWP_URL_OK"),
				"DESCR" => GetMessage("SWMWP_URL_OK_DESC"),
				"VALUE" => "",
				"TYPE" => "",
			),
		"FAIL_URL" => Array(
				"NAME" => GetMessage("SWMWP_URL_ERROR"),
				"DESCR" => GetMessage("SWMWP_URL_ERROR_DESC"),
				"VALUE" => "",
				"TYPE" => "",
			),
		"LMI_PAYER_PHONE_NUMBER" => Array(
				"NAME" => GetMessage("SWMWP_PHONE"),
				"DESCR" => "",
				"VALUE" => "PHONE",
				"TYPE" => "PROPERTY",
			),
		"LMI_PAYER_EMAIL" => Array(
				"NAME" => GetMessage("SWMWP_MAIL"),
				"DESCR" => "",
				"VALUE" => "EMAIL",
				"TYPE" => "PROPERTY",
			),
		"CHANGE_STATUS_PAY" => array(
				"NAME" => GetMessage("PYM_CHANGE_STATUS_PAY"),
				"DESCR" => GetMessage("PYM_CHANGE_STATUS_PAY_DESC"),
				"VALUE" => "Y",
				"TYPE" => ""
			),

	);
?>