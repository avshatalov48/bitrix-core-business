<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SBBS_DEFAULT_TEMPLATE_NAME"),
	"TYPE" => "mail",
	"DESCRIPTION" => GetMessage("SBBS_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_basket.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_basket",
			"NAME" => GetMessage("SBBS_NAME")
		)
	),
);
?>