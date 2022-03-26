<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPSSA_DESCR_NAME_1"),
	"DESCRIPTION" => GetMessage("BPSSA_DESCR_DESCR_1"),
	"TYPE" => "activity",
	"CATEGORY" => array(
		"ID" => "logic",
	),
	"CLASS" => "SetStateActivity",
	"JSCLASS" => "SetStateActivity",
);
?>
