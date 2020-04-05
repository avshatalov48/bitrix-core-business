<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/webmoney_pci.php"));

$psTitle = GetMessage("SWMPP_DTITLE");
$psDescription = GetMessage("SWMPP_DDESCR");

$arPSCorrespondence = array(
		"ORDER_ID" => array(
				"NAME" => GetMessage("SWMPP_ORDER_ID"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("SWMPP_SUMMA"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"ACC_NUMBER" => array(
				"NAME" => GetMessage("SWMPP_NUMBER"),
				"DESCR" => GetMessage("SWMPP_NUMBER_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"TEST_MODE" => array(
				"NAME" => GetMessage("SWMPP_TEST"),
				"DESCR" => GetMessage("SWMPP_TEST_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"PATH_TO_RESULT" => array(
				"NAME" => GetMessage("SWMPP_DIR"),
				"DESCR" => GetMessage("SWMPP_DIR_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"CNST_SECRET_KEY" => array(
				"NAME" => GetMessage("SWMPP_PASSW"),
				"DESCR" => GetMessage("SWMPP_PASSW_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			)
	);
?>