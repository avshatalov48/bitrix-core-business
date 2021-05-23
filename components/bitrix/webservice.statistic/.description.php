<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("BX_WS_STAT_TITLE"),
	"DESCRIPTION" => GetMessage("BX_WS_STAT_DESCR"),
	"ICON" => "/images/ws.checkauth.gif",
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