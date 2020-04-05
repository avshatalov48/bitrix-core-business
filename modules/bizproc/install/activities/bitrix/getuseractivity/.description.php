<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPGUA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPGUA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "GetUserActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "other",
	),
	"RETURN" => array(
		"GetUser" => array(
			"NAME" => GetMessage("BPGUA_DESCR_RU"),
			"TYPE" => "user",
		),
	),
);
?>