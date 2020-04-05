<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("MYMV_COMP_NAME"),
	"DESCRIPTION" => GetMessage("MYMV_COMP_DESCRIPTION"),
	"ICON" => "/images/map_view.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"NAME" => GetMessage("MAIN_G_CONTENT"),
		"CHILD" => array(
			"ID" => "google_map",
			"NAME" => GetMessage("MAIN_GOOGLE_MAP_SERVICE"),
		)
	),
);

?>