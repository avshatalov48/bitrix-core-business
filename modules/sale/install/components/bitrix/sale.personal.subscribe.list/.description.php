<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SPSL_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SPSL_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_subscr_list.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_personal",
			"NAME" => GetMessage("SPSL_NAME")
		)
	),
);
?>