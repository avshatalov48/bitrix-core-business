<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("BPWC_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("BPWC_COMPONENT_NAME_DESCRIPTION"),
	"ICON" => "/images/comp.gif",
	"COMPLEX" => "Y",
	"PATH" => array(
		"ID" => "communication",
		"CHILD" => array(
			"ID" => "bp",
			"NAME" => GetMessage("BPWC_GROUP")
		)
	),
);
?>