<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPSVA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPSVA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "SetVariableActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "other",
	),
);
?>
