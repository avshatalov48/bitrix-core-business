<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CP_CATALOG_STORE_CSD_NAME"),
	"DESCRIPTION" => GetMessage("CP_CATALOG_STORE_CSD_DESCRIPTION"),
	"ICON" => "/images/store_detail.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 30,
	"PATH" => array(
		"ID" => "e-store",
		"NAME" => GetMessage('CP_CATALOG_STORE_MAIN_SECTION'),
		"CHILD" => array(
			"ID" => "catalog-store",
			"NAME" => GetMessage("CP_CATALOG_STORE_STORE_SECTION"),
			"SORT" => 500,
		)
	)
);
?>