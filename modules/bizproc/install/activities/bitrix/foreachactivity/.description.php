<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPFEA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPFEA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "ForEachActivity",
	"JSCLASS" => "ForEachActivity",
	"CATEGORY" => ["ID" => "logic"],
	"RETURN" => array(
		"Key" => array(
			"NAME" => GetMessage("BPFEA_DESCR_RETURN_KEY"),
			"TYPE" => "string",
		),
		"Value" => array(
			"NAME" => GetMessage("BPFEA_DESCR_RETURN_VALUE"),
			"TYPE" => "mixed",
		),
	)
);