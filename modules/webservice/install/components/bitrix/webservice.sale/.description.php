<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("BX_WS_SALE_TITLE"),
	"DESCRIPTION" => GetMessage("BX_WS_SALE_DESCR"),
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "webservice",
			"NAME" => GetMessage("BX_WS_CHECKAUTH")
		)
	),
);
?>