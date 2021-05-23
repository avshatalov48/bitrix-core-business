<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IBLOCK_SET_NAME"),
	"DESCRIPTION" => GetMessage("IBLOCK_SET_DESCRIPTION"),
	"ICON" => "/images/cnst.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 100,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog",
			"NAME" => GetMessage("T_IBLOCK_DESC_SET"),
			"SORT" => 30,
		)
	),
);

?>