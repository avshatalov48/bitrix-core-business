<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPIMNA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPIMNA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "IMNotifyActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
);
?>
