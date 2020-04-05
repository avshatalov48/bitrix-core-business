<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SOP_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SOP_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_order",
			"NAME" => GetMessage("SOP_NAME")
		)
	),
);
?>