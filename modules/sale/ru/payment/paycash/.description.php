<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/paycash.php"));

$psTitle = GetMessage("SPCP_DTITLE");
$psDescription = GetMessage("SPCP_DDESCR");

$arPSCorrespondence = array(
		"SHOP_ACCOUNT" => array(
				"NAME" => GetMessage("SPCP_CODE"),
				"DESCR" => GetMessage("SPCP_CODE_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_KEY_ID" => array(
				"NAME" => GetMessage("SPCP_CODEKEY"),
				"DESCR" => GetMessage("SPCP_CODEKEY_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_KEY" => array(
				"NAME" => GetMessage("SPCP_KEY"),
				"DESCR" => GetMessage("SPCP_KEY_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			)
	);
?>