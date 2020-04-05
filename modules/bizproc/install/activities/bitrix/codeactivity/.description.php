<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPCA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPCA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "CodeActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "other",
	),
	"FILTER" => array(
		'EXCLUDE' => CBPHelper::DISTR_B24
	),
);
?>
