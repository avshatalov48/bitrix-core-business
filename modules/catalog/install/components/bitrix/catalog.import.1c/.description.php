<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BCI1_NAME"),
	"DESCRIPTION" => GetMessage("CD_BCI1_DESCRIPTION"),
	"ICON" => "/images/1c-imp.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 120,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog-1c",
			"NAME" => GetMessage("CP_CATALOG_1C_PARENT_SECTION"),
			"SORT" => 300,
		),
	),
);

?>