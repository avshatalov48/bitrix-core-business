<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SPOL_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SPOL_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_order_tbl.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_personal",
			"NAME" => GetMessage("SPOL_NAME")
		)
	),
);
?>