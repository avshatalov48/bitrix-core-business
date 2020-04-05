<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SBBL_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SBBL_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_basket.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_basket",
			"NAME" => GetMessage("SBBL_NAME")
		)
	),
);
?>