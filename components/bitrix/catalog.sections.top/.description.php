<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IBLOCK_SECTIONS_TOP_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("IBLOCK_SECTIONS_TOP_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sections_top.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 100,
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