<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SPCL_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SPCL_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_cards.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_personal",
			"NAME" => GetMessage("SPCL_NAME")
		)
	),
);
?>