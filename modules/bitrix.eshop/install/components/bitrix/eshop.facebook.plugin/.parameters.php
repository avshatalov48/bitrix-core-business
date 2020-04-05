<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"ESHOP_FACEBOOK_LINK" => Array(
			"NAME" => GetMessage("ESHOP_FACEBOOK_LINK"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"ESHOP_PLUGIN_WIDTH" => Array(
			"NAME" => GetMessage("ESHOP_PLUGIN_WIDTH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "230",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"ESHOP_PLUGIN_HEIGHT" => Array(
			"NAME" => GetMessage("ESHOP_PLUGIN_HEIGHT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
	)
);
?>