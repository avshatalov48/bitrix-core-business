<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("FORUM_LIST"), 
	"DESCRIPTION" => GetMessage("FORUM_LIST_DESCRIPTION"), 
	"ICON" => "/images/icon.gif",
	"PATH" => array(
		"ID" => "communication", 
		"CHILD" => array(
			"ID" => "socialnetwork",
			"NAME" => GetMessage("SONET_NAME")
		)
	),
);
?>