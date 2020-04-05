<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CPV_SECTION_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("CPV_SECTION_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/catalog_viewed.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 10,
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