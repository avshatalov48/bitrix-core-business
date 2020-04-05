<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPCA1_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPCA1_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "CalendarActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
	'EXCLUDED' => true
);
?>