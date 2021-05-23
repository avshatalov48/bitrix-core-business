<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BLLE_NAME"),
	"DESCRIPTION" => GetMessage("CD_BLLE_DESCRIPTION"),
	"ICON" => "/images/lists_list_edit.gif",
	"SORT" => 60,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "lists",
			"NAME" => GetMessage("CD_BLLE_LISTS"),
			"SORT" => 35,
		)
	),
);

?>