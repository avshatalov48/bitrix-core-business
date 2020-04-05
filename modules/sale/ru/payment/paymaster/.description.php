<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

include \Bitrix\Main\Application::getDocumentRoot().'/bitrix/modules/sale/handlers/paysystem/paymaster/.description.php';

return;
include(GetLangFileName(dirname(__FILE__)."/", "/paymaster.php"));

$psTitle = GetMessage("SWMWP_DTITLE");
$psDescription  = GetMessage("SWMWP_DDESCR");

$arPSCorrespondence = array(
		"SHOP_ACCT" => array(
				"NAME" => GetMessage("SWMWP_ID"),
				"DESCR" => GetMessage("SWMWP_ID_DESC"),
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
				"NAME" => GetMessage("SWMWP_SECRETKEY"),
				"DESCR" => GetMessage("SWMWP_SECRETKEY_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"ORDER_ID" => Array(
				"NAME" => GetMessage("SWMWP_IDORDER"),
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
		"CURRENCY" => Array(
				"NAME" => GetMessage("SWMWP_VALUTE"),
				"DESCR" => "",
 				"VALUE" => "CURRENCY",
 				"TYPE" => "ORDER",
			),
		"RESULT_URL" => Array(
				"NAME" => GetMessage("SWMWP_ADRES_NOTIFY"),
				"DESCR" => GetMessage("SWMWP_ADRES_NOTIFY_DESC"),
 				"VALUE" => "",
 				"TYPE" => "",
			),
		"SUCCESS_URL" => Array(
				"NAME" => GetMessage("SWMWP_ADRES_OK"),
				"DESCR" => GetMessage("SWMWP_ADRES_OK_DESC"),
 				"VALUE" => "",
 				"TYPE" => "",
			),
		"FAIL_URL" => Array(
				"NAME" => GetMessage("SWMWP_ADRES_ERROR"),
				"DESCR" => GetMessage("SWMWP_ADRES_ERROR_DESC"),
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

	);
?>