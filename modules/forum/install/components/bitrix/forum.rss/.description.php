<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("F_TITLE"),
	"DESCRIPTION" => GetMessage("F_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 300,
	"PATH" => array(
		"ID" => "communication",
		"CHILD" => array(
			"ID" => "forum",
			"NAME" => GetMessage("FORUM")
		)
	),
);
?>