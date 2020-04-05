<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPTA1_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPTA1_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "TaskActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
	'EXCLUDED' => true
);
?>