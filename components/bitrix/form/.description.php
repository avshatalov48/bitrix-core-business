<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("FORM_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("FORM_COMPONENT_DESCR"),
	"ICON" => "/images/form.gif",
	"COMPLEX" => "Y",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "form",
			"NAME" => GetMessage("FORM_SERVICE"),
			"CHILD" => array(
				"ID" => "form_cmpx",
			),
		),
	),
);
?>