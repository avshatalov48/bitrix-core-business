<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("WZ_NAME"),
	"DESCRIPTION" => GetMessage("WZ_DESC"),
	"ICON" => "/images/wizard.png",
	"SORT" => 200,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "support",
			"NAME" => GetMessage("SUPPORT_SERVICE")
		),
	),
);

?>
