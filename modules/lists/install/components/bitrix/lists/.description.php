<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BL_NAME"),
	"DESCRIPTION" => GetMessage("CD_BL_DESCRIPTION"),
	"ICON" => "/images/lists.gif",
	"SORT" => 10,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "lists",
			"NAME" => GetMessage("CD_BL_LISTS"),
			"SORT" => 35,
		)
	),
);

?>