<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SPSC_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SPSC_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_subscr_cancel.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_personal",
			"NAME" => GetMessage("SPSC_NAME")
		)
	),
);
?>