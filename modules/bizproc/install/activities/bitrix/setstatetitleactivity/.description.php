<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPSSA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPSSA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
	"CLASS" => "SetStateTitleActivity",
	"JSCLASS" => "BizProcActivity",
);
?>
