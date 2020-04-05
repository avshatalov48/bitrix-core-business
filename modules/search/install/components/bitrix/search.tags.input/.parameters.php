<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("search"))
	return;

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
		"SITE_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_SITE_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => SITE_ID,
		)
	),
);

CSearchParameters::AddFilterParams($arComponentParameters, $arCurrentValues, "arrFILTER", "DATA_SOURCE", "N");
?>