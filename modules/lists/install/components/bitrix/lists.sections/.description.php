<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BLS_NAME"),
	"DESCRIPTION" => GetMessage("CD_BLS_DESCRIPTION"),
	"ICON" => "/images/lists_sections.gif",
	"SORT" => 40,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "lists",
			"NAME" => GetMessage("CD_BLS_LISTS"),
			"SORT" => 35,
		)
	),
);

?>