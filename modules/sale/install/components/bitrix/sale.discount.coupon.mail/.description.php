<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" =>  GetMessage("SB_DEFAULT_TEMPLATE_NAME"),
	"TYPE" => "mail",
	"DESCRIPTION" => GetMessage("SB_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_rec.gif",
	"SORT" => 30,
	"PATH" => array(
		"ID" => "e-store",
		"NAME" => GetMessage('CP_CATALOG_SERVICES_MAIN_SECTION'),
		"CHILD" => array(
			"ID" => "catalog-services",
			"NAME" => GetMessage('CP_CATALOG_SERVICES_PARENT_SECTION'),
			"SORT" => 500,
		)
	)
);
?>