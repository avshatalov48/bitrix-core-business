<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SALE_SLS_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("SALE_SLS_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/component_icon.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_order",
			"NAME" => GetMessage("SAL_NAME")
		)
	),
);
?>