<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("MOBILEAPP_MM_NAME"),
	"DESCRIPTION" => GetMessage("MOBILEAPP_MM_DESCRIPT"),
	"ICON" => "/images/menu.gif",
	"PATH" => array(
		"ID" => "utility",
		"CHILD" => array(
			"ID" => "navigation",
			"NAME" => GetMessage("MAIN_NAVIGATION_SERVICE")
		)
	),
	"CACHE_PATH" => "Y",
);
?>