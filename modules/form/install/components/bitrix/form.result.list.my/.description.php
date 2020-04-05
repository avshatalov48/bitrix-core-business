<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("FRLM_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("FRLM_COMPONENT_DESCR"),
	"ICON" => "/images/result_list_my.gif",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "form",
			"NAME" => GetMessage("FORM_SERVICE"),
		)
	),
);
?>