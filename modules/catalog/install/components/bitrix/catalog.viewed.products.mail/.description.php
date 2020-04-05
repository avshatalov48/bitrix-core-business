<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CVPM_DEFAULT_TEMPLATE_NAME"),
	"TYPE" => "mail",
	"DESCRIPTION" => GetMessage("CVPM_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/viewed_products.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "viewed_products",
			"NAME" => GetMessage("CVPM_NAME")
		)
	),
);
?>