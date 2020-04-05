<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPDA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPDA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "DelayActivity",
	"JSCLASS" => "DelayActivity",
	"CATEGORY" => array(
		"ID" => "other",
	),
);
?>
