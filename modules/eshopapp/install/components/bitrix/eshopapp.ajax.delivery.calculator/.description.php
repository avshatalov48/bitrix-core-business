<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SADC_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SADC_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_ajax_delcalc.gif",
	"PATH" => array(
		"ID" => GetMessage("T_ESHOPAPP"),
		"CHILD" => array(
			"ID" => "eshopapp_sale_order",
			"NAME" => GetMessage("SAL_NAME")
		)
	),
);
?>