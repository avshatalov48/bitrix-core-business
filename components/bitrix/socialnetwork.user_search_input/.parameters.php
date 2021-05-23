<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"NAME" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "TAG",
		),
		"VALUE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_VALUE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"FUNCTION" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_FUNCTION"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
/* modified by wladart */		
		"EXTRANET" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_EXTRANET"),
			"TYPE" => "STRING",
			"DEFAULT" => "I",
		),
/* --modified by wladart */		
		
	),
);
?>