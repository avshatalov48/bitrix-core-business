<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"THUMBNAIL_SIZE" => array(
		"NAME" => GetMessage("P_THUMBS_SIZE"),
		"TYPE" => "STRING",
		"DEFAULT" => "120"),
	"SHOW_PAGE_NAVIGATION" => array(
		"NAME" => GetMessage("P_SHOW_PAGE_NAVIGATION"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"none" => GetMessage("P_SHOW_PAGE_NAVIGATION_NONE"),
			"top" => GetMessage("P_SHOW_PAGE_NAVIGATION_TOP"),
			"bottom" => GetMessage("P_SHOW_PAGE_NAVIGATION_BOTTOM"),
			"both" => GetMessage("P_SHOW_PAGE_NAVIGATION_BOTH")),
		"DEFAULT" => "bottom"),
	"SHOW_RATING" => array( 
		"NAME" => GetMessage("P_SHOW_RATING"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N"),
	"SHOW_SHOWS" => array( 
		"NAME" => GetMessage("P_SHOW_SHOWS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N"),
	"SHOW_COMMENTS" => array( 
		"NAME" => GetMessage("P_SHOW_COMMENTS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N"),
	"MAX_VOTE" => array(
		"NAME" => GetMessage("IBLOCK_MAX_VOTE"),
		"TYPE" => "STRING",
		"DEFAULT" => "5"), 
	"VOTE_NAMES" => array(
		"NAME" => GetMessage("IBLOCK_VOTE_NAMES"),
		"TYPE" => "STRING",
		"VALUES" => array(),
		"MULTIPLE" => "Y",
		"DEFAULT" => array("1","2","3","4","5"),
		"ADDITIONAL_VALUES" => "Y"), 
	"DISPLAY_AS_RATING" => array(
		"NAME" => GetMessage("TP_CBIV_DISPLAY_AS_RATING"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"rating" => GetMessage("TP_CBIV_RATING"),
			"vote_avg" => GetMessage("TP_CBIV_AVERAGE"),
			"rating_main" => GetMessage("TP_CBIV_RATING_MAIN"),
		),
		"DEFAULT" => "rating",
	), 
	"RATING_MAIN_TYPE" => array(
		"NAME" => GetMessage("RATING_MAIN_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"" => GetMessage("RATING_MAIN_TYPE_CONFIG"),
			"like" => GetMessage("RATING_MAIN_TYPE_LIKE_TEXT"),
			"like_graphic" => GetMessage("RATING_MAIN_TYPE_LIKE_GRAPHIC"),
			"standart_text" => GetMessage("RATING_MAIN_TYPE_STANDART_TEXT"),
			"standart" => GetMessage("RATING_MAIN_TYPE_STANDART_GRAPHIC"),
		),
		"DEFAULT" => "",
	)
);
?>