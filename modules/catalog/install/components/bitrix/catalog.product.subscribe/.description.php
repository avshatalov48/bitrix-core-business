<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CATALOG_PRODUCT_SUBSCRIBE_NAME"),
	"DESCRIPTION" => GetMessage("CATALOG_PRODUCT_SUBSCRIBE_DESCRIPTION"),
	"ICON" => "/images/sale_viewed.gif",
	"SORT" => 50,
	"PATH" => array(
		"ID" => "e-store",
		"NAME" => GetMessage('CP_CATALOG_SERVICES_MAIN_SECTION'),
		"CHILD" => array(
			"ID" => "catalog-services",
			"NAME" => GetMessage("CP_CATALOG_SERVICES_PARENT_SECTION"),
			"SORT" => 500,
		)
	)
);