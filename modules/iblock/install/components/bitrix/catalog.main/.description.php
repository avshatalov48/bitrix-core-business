<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IBLOCK_MAIN_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("IBLOCK_MAIN_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/cat_all.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 80,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog",
			"NAME" => GetMessage("T_IBLOCK_DESC_CATALOG"),
			"SORT" => 30,
		)
	),
);

?>