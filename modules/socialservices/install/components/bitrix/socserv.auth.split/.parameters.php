<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arComponentParameters = array(
	"PARAMETERS" => array(
		"SHOW_PROFILES" => array(
			"NAME" => GetMessage("SOCSERV_SHOW_PROFILES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),

		"ALLOW_DELETE" => array(
			"NAME" => GetMessage("SOCSERV_ALLOW_DELETE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
	),
);
?>