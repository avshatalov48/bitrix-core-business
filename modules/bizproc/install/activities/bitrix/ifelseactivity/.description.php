<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPIEA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPIEA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS"=>"IfElseActivity",
	"JSCLASS"=>"IfElseActivity",
	"CATEGORY" => array(
		"ID" => "logic",
	),
);
?>
