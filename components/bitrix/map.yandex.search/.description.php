<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("MYMS_COMP_NAME"),
	"DESCRIPTION" => GetMessage("MYMS_COMP_DESCRIPTION"),
	"ICON" => "/images/map_search.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"NAME" => GetMessage("MAIN_Y_CONTENT"),
		"CHILD" => array(
			"ID" => "yandex_map",
			"NAME" => GetMessage("MAIN_YANDEX_MAP_SERVICE"),
		)
	),
);

?>