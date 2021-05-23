<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SPA_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SPA_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_account.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_personal",
			"NAME" => GetMessage("SPA_NAME")
		)
	),
);
?>