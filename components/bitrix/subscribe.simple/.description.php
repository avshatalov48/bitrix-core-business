<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BSS_NAME"),
	"DESCRIPTION" => GetMessage("CD_BSS_DESCRIPTION"),
	"ICON" => "/images/subscr_edit.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "subscribe",
			"NAME" => GetMessage("CD_BSS_SERVICE")
		)
	),
);
?>