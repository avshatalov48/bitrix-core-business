<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" =>  GetMessage("SBF_PERSONAL_SECTION_TEMPLATE_NAME_MAIL"),
	"TYPE" => "mail",
	"DESCRIPTION" => GetMessage("SBF_PERSONAL_SECTION_TEMPLATE_DESCRIPTION"),
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