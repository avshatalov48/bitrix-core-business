<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("P_NAME"),
	"DESCRIPTION" => GetMessage("P_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 20,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "photogallery",
			"NAME" => GetMessage("P_PHOTOGALLERY"),
			"SORT" => 20,
			"CHILD" => array(
				"ID" => "photo_gallery",
			),
		),
	),
);

?>