<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BLEN_NAME"),
	"DESCRIPTION" => GetMessage("CD_BLEN_DESCRIPTION"),
	"ICON" => "/images/lists_element_navchain.gif",
	"SORT" => 90,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "lists",
			"NAME" => GetMessage("CD_BLEN_LISTS"),
			"SORT" => 35,
		)
	),
);

?>