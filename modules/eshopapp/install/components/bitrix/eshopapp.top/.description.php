<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("T_IBLOCK_DESC_CR_LIST"),
	"DESCRIPTION" => GetMessage("T_IBLOCK_DESC_CR_DESC"),
	"ICON" => "/images/cat_all.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 90,
	"PATH" => array(
		"ID" => GetMessage("T_ESHOPAPP"),
		"CHILD" => array(
			"ID" => "eshopapp_catalog",
			"NAME" => GetMessage("T_IBLOCK_DESC_CATALOG"),
			"SORT" => 30,
		)
	),
);

?>