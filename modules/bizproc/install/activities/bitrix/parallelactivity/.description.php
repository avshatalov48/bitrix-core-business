<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPPA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPPA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "ParallelActivity",
	"JSCLASS" => "ParallelActivity",
	"CATEGORY" => array(
		"ID" => "logic",
	),
);
?>
