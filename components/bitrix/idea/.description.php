<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("IDEA_COMPONENT"),
	"DESCRIPTION" => GetMessage("IDEA_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"COMPLEX" => "Y",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "idea",
			"NAME" => GetMessage("IDEA")
		)
	),
);
?>