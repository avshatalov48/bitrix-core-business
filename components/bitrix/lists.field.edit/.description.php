<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BLFE_NAME"),
	"DESCRIPTION" => GetMessage("CD_BLFE_DESCRIPTION"),
	"ICON" => "/images/lists_field_edit.gif",
	"SORT" => 80,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "lists",
			"NAME" => GetMessage("CD_BLFE_LISTS"),
			"SORT" => 35,
		)
	),
);

?>