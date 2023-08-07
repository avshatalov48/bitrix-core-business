<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPCAL_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPCAL_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "LogActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "other",
	),
	"RETURN" => array(
		"Report" => array(
			"NAME" => GetMessage("BPCAL_DESCR_REPORT"),
			"TYPE" => "string",
		),
	),
);
?>
