<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SUP_EDIT_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SUP_EDIT_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/support_edit.gif",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "support",
			"NAME" => GetMessage("SUPPORT_SERVICE")
		)
	),
);


?>