<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/impexbank.php"));

$psTitle = GetMessage("SIBP_DTITLE");
$psDescription = GetMessage("SIBP_DDESCR");

$arPSCorrespondence = array(
		"SHOP_ACCOUNT" => array(
				"NAME" => GetMessage("SIBP_CODE"),
				"DESCR" => GetMessage("SIBP_CODE"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_NAME" => array(
				"NAME" => GetMessage("SIBP_NAME"),
				"DESCR" => GetMessage("SIBP_NAME_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			)
	);
?>