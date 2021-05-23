<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"SHOW_PAGE_NAVIGATION" => array(
		"NAME" => GetMessage("P_SHOW_PAGE_NAVIGATION"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"none" => GetMessage("P_SHOW_PAGE_NAVIGATION_NONE"),
			"top" => GetMessage("P_SHOW_PAGE_NAVIGATION_TOP"),
			"bottom" => GetMessage("P_SHOW_PAGE_NAVIGATION_BOTTOM"),
			"both" => GetMessage("P_SHOW_PAGE_NAVIGATION_BOTH")),
		"DEFAULT" => "none",
	),
	"ELEMENT_ID" => array(
		"NAME" => GetMessage("P_ELEMENT_ID"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	),
	"SLIDER_COUNT_CELL" => array(
		"NAME" => GetMessage("P_SLIDER_COUNT_CELL"),
		"TYPE" => "STRING",
		"DEFAULT" => "4",
	),
);
?>