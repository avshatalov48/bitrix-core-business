<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("BX_WS_NAME"),
	"DESCRIPTION" => GetMessage("BX_WS_DESCRIPTION"),
	"ICON" => "/images/ws.server.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "webservice",
			"NAME" => GetMessage("BX_WS")
		)
	),
);

?>