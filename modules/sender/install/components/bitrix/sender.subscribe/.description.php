<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BSF_NAME"),
	"DESCRIPTION" => GetMessage("CD_BSF_DESCRIPTION"),
	"ICON" => "",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "sender",
			"NAME" => GetMessage("CD_BSF_SERVICE")
		)
	),
);

?>