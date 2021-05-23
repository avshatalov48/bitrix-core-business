<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SUP_LIST_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SUP_LIST_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/support_list.gif",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "support",
			"NAME" => GetMessage("SUPPORT_SERVICE")
		)
	),
);


?>