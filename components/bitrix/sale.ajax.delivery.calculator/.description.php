<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SADC_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SADC_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_ajax_delcalc.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_order",
			"NAME" => GetMessage("SAL_NAME")
		)
	),
);
?>