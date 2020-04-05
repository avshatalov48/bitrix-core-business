<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("socialnetwork"))
	return false;

$arComponentParameters = Array(
	"GROUPS" => array(
		"VARIABLE_ALIASES" => array(
			"NAME" => GetMessage("SONET_VARIABLE_ALIASES"),
		),
	),
	"PARAMETERS" => Array(
		"EVENTS_VAR" => Array(
			"NAME" => GetMessage("SONET_EVENTS_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "events",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PATH_TO_USER" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_USER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_GROUP" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_GROUP"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"LOG_DATE_DAYS" => Array(
			"NAME" => GetMessage("SONET_LOG_DATE_DAYS"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "7",
			"COLS" => 10,
			"PARENT" => "DATA_SOURCE",
		),
		"RSS_TTL" => Array(
			"NAME" => GetMessage("SONET_RSS_TTL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "60",
			"COLS" => 3,
			"PARENT" => "DATA_SOURCE",
		),		
	)
);
?>