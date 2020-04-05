<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/sberbank.php"));

$psTitle = GetMessage("SSBP_DTITLE");
$psDescription = GetMessage("SSBP_DDESCR");

$arPSCorrespondence = array(
		"SELLER_PARAMS" => array(
				"NAME" => GetMessage("SSBP_RECIPES"),
				"DESCR" => GetMessage("SSBP_RECIPES_DESC"),
				"VALUE" => GetMessage("SSBP_RECIPES_VAL"),
				"TYPE" => ""
			),
		"PAYER_NAME" => array(
				"NAME" => GetMessage("SSBP_NAME"),
				"DESCR" => GetMessage("SSBP_NAME_DESC"),
				"VALUE" => "PAYER_NAME",
				"TYPE" => "PROPERTY"
			)
	);
?>