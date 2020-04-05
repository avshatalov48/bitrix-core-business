<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"NOTIFY_ID" => Array(
			"NAME" => GetMessage("NOTIFY_ID"),
			"TYPE" => "STRING",
			"PARENT" => "BASE",
			"DEFAULT" => "",
		),
		"NOTIFY_URL" => array(
			"NAME" => GetMessage("NOTIFY_URL"),
			"TYPE" => "STRING",
			"PARENT" => "BASE",
			"DEFAULT" => "",
		),
		"NOTIFY_USE_CAPTHA" => Array(
			"NAME"=> GetMessage("NOTIFY_USE_CAPTHA"),
			"TYPE" => "CHECKBOX",
			"PARENT" => "BASE",
			"DEFAULT"=>"Y",
		),
	)
);
?>