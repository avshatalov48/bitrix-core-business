<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CP_CATALOG_STORE_CS_NAME"),
	"DESCRIPTION" => GetMessage("CP_CATALOG_STORE_CS_DESCRIPTION"),
	"ICON" => "/images/cat_all.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 10,
	"PATH" => array(
		"ID" => "e-store",
		"NAME" => GetMessage('CP_CATALOG_STORE_MAIN_SECTION'),
		"CHILD" => array(
			"ID" => "catalog-store",
			"NAME" => GetMessage("CP_CATALOG_STORE_STORE_SECTION"),
			"SORT" => 500,
		)
	),
	"COMPLEX" => "Y"
);
?>