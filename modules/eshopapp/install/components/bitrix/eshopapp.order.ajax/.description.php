<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SOF_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SOF_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_order_full.gif",
	"PATH" => array(
		"ID" => GetMessage("T_ESHOPAPP"),
		"CHILD" => array(
			"ID" => "eshopapp_sale_order",
			"NAME" => GetMessage("SOF_NAME")
		)
	),
);
?>