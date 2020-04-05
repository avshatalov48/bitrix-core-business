<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SBB_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SBB_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_basket.gif",
	"PATH" => array(
		"ID" => GetMessage("T_ESHOP"),
		"CHILD" => array(
			"ID" => "eshop_facebook_plugin",
			"NAME" => GetMessage("SBB_NAME")
		)
	),
);
?>