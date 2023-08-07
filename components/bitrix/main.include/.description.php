<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("MAIN_INCLUDE_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("MAIN_INCLUDE_COMPONENT_DESCR"),
	"ICON" => "/images/include.gif",
	"PATH" => array(
		"ID" => "utility",
		"CHILD" => array(
			"ID" => "include_area",
			"NAME" => GetMessage("MAIN_INCLUDE_GROUP_NAME"),
		),
	),
);
?>