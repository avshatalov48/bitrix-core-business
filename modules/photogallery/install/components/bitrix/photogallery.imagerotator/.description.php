<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IBLOCK_ELEMENT_TEMPLATE_NAME").' '.GetMessage("IMAGEROTATOR_DEPRECATED"),
	"DESCRIPTION" => GetMessage("IBLOCK_ELEMENT_TEMPLATE_DESCRIPTION").' '.GetMessage("IMAGEROTATOR_DEPRECATED_2"),
	"ICON" => "/images/icon.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 30,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "photogallery",
			"NAME" => GetMessage("T_IBLOCK_DESC_PHOTO"),
			"SORT" => 20,
			"CHILD" => array(
				"ID" => "photo_gallery",
			),
		),
	)
);

?>