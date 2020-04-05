<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IBLOCK_CB_DESC_NAME"),
	"DESCRIPTION" => GetMessage("IBLOCK_CB_DESC_DESC"),
	"ICON" => "/images/brandblocks.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 40,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog",
			"NAME" => GetMessage("IBLOCK_CB_DESC_CATALOG"),
			"SORT" => 30
		)
	)
);
?>