<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("BMS_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("BMS_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/search.gif",
	"SORT" => 370,
	"PATH" => array(
		"ID" => "communication",
		"CHILD" => array(
			"ID" => "blog",
			"NAME" => GetMessage("BMS_NAME")
		)
	),
);
?>