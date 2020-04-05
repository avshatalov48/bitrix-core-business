<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPAA2_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPAA2_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "AbsenceActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
);
?>