<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arComponentParameters = array(
	"PARAMETERS" => array(
		"REDIRECT_PAGE" => array(
			"NAME" => GetMessage("SPCD1_REDIRECT_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => '={$_REQUEST["REDIRECT_PAGE"]}',
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"AGREEMENT_TEXT_FILE" => array(
			"NAME" => GetMessage("SPCD1_AGREEMENT_TEXT_FILE"),
			"TYPE" => "STRING",
			"DEFAULT" => '/bitrix/components/bitrix/sale.affiliate.register/agreement-'.SITE_ID.'.htm',
			"PARENT" => "BASE",
		),
		
		"SET_TITLE" => array(),
	),
);
?>