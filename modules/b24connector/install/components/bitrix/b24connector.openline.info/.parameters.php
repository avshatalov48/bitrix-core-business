<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arComponentParameters = array(
	"PARAMETERS" => array(
		"DATA" => array(
			"NAME" => GetMessage("COMP_AUTH_OPENLINE_DATA"), 
			"TYPE" => "TEXT",
			"ROWS" => "10",
			"DEFAULT" => "",
		),
		"GA_MARK" => array(
			"NAME" => GetMessage("COMP_AUTH_OPENLINE_GA_MARK"), 
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
	),
);
?>