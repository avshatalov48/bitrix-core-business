<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("PLAYER_COMPONENT"),
	"DESCRIPTION" => GetMessage("PLAYER_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"COMPLEX" => "N",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "media",
			"NAME" => GetMessage("MEDIA")
		)
	),
);
?>