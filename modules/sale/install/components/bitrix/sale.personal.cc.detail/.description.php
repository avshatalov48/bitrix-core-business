<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SPCD_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SPCD_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_card_detail.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_personal",
			"NAME" => GetMessage("SPCD_NAME")
		)
	),
);
?>