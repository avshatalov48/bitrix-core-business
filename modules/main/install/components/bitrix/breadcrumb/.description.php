<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("MAIN_BREADCRUMB_NAME"),
	"DESCRIPTION" => GetMessage("MAIN_BREADCRUMB_DESC"),
	"ICON" => "/images/breadcrumb.gif",
	"PATH" => array(
		"ID" => "utility",
		"CHILD" => array(
			"ID" => "navigation",
			"NAME" => GetMessage("MAIN_NAVIGATION_SERVICE")
		)
	),
);

?>