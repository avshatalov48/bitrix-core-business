<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SUP_SUPPORT_NAME"),
	"DESCRIPTION" => GetMessage("SUP_SUPPORT_DESCRIPTION"),
	"ICON" => "/images/support.gif",
	"COMPLEX" => "Y",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "support",
			"NAME" => GetMessage("SUPPORT_SERVICE")
		)
	),
);
?>