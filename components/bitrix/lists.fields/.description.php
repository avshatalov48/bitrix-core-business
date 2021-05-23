<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BLF_NAME"),
	"DESCRIPTION" => GetMessage("CD_BLF_DESCRIPTION"),
	"ICON" => "/images/lists_fields.gif",
	"SORT" => 70,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "lists",
			"NAME" => GetMessage("CD_BLF_LISTS"),
			"SORT" => 35,
		)
	),
);

?>