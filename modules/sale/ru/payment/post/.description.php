<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/post.php"));

$psTitle = GetMessage("SPPP_DTITLE");
$psDescription = GetMessage("SPPP_DDESCR");

$arPSCorrespondence = array(
		"POST_ADDRESS" => array(
				"NAME" => GetMessage("SPPP_ADRES"),
				"DESCR" => GetMessage("SPPP_ADRES_DESC"),
				"VALUE" => GetMessage("SPPP_ADRES_VALUE"),
				"TYPE" => ""
			),
		"PAYER_NAME" => array(
				"NAME" => GetMessage("SPPP_PAYER"),
				"DESCR" => GetMessage("SPPP_PAYER_DESC"),
				"VALUE" => "PAYER_NAME",
				"TYPE" => "PROPERTY"
			)
	);
?>