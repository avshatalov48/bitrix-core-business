<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
/*	"SHOW_PAGE_NAVIGATION" => array(
		"NAME" => GetMessage("P_SHOW_PAGE_NAVIGATION"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"top" => GetMessage("P_SHOW_PAGE_NAVIGATION_TOP"),
			"bottom" => GetMessage("P_SHOW_PAGE_NAVIGATION_BOTTOM")),
		"DEFAULT" => "bottom", 
		"MULTIPLE" => "Y"),
*/	"SHOW_LINK_ON_MAIN_PAGE" => array(
		"NAME" => GetMessage("P_SHOW_LINK_ON_MAIN_PAGE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"id" => GetMessage("P_LINK_NEW"), 
			"shows" => GetMessage("P_LINK_SHOWS"),
			"rating" => GetMessage("P_LINK_RATING"), 
			"comments" => GetMessage("P_LINK_COMMENTS")),
		"DEFAULT" => array("id", "rating", "comments", "shows"),
		"MULTIPLE" => "Y"),
	"WATERMARK" => array(
        "NAME" => GetMessage("P_SHOW_WATERMARK"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"REFRESH" => "Y")
	);
if ($arCurrentValues["WATERMARK"] != "N"):
$arTemplateParameters = $arTemplateParameters + array(
	"WATERMARK_COLORS" => Array(
		"NAME" => GetMessage("P_WATERMARK_COLORS"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"FF0000" => GetMessage("P_COLOR_FF0000"), 
			"FFA500" => GetMessage("P_COLOR_FFA500"), 
			"FFFF00" => GetMessage("P_COLOR_FFFF00"), 
			"008000" => GetMessage("P_COLOR_008000"), 
			"00FFFF" => GetMessage("P_COLOR_00FFFF"), 
			"800080" => GetMessage("P_COLOR_800080"), 
			"FFFFFF" => GetMessage("P_COLOR_FFFFFF"),
			"000000" => GetMessage("P_COLOR_000000")),
		"DEFAULT" => array("FF0000", "FFFF00", "FFFFFF", "000000"),
		"ADDITIONAL_VALUES" => "Y",
		"MULTIPLE" => "Y"));
endif;
$arTemplateParameters = $arTemplateParameters + array(
	"TEMPLATE_LIST" => Array(
		"NAME" => GetMessage("P_TEMPLATE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			".default" => GetMessage("P_TEMLATE_DEFAULT"), 
			"table" => GetMessage("P_TEMLATE_TABLE")),
		"DEFAULT" => array("")), 
	"CELL_COUNT" => array(
		"NAME" => GetMessage("P_TEMPLATE_CELL_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => "0"), 
	"SLIDER_COUNT_CELL" => array(
		"NAME" => GetMessage("P_SLIDER_COUNT_CELL"),
		"TYPE" => "STRING",
		"DEFAULT" => "4")
);

?>