<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
		"TAGS_SORT" => array(
			"NAME" => GetMessage("SEARCH_SORT"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => array("NAME"=>GetMessage("SEARCH_NAME"), "CNT"=>GetMessage("SEARCH_CNT")),
			"DEFAULT" => "NAME",
		),
		"TAGS_PAGE_ELEMENTS" => array(
			"NAME" => GetMessage("SEARCH_PAGE_ELEMENTS"),
			"TYPE" => "STRING",
			"DEFAULT" => "150",
		),
		"TAGS_PERIOD" => array(
			"NAME" => GetMessage("SEARCH_PERIOD"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"TAGS_URL_SEARCH" => array(
			"NAME" => GetMessage("SEARCH_URL_SEARCH"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"ITEM_URL" => array(
			"NAME" => GetMessage("SEARCH_ITEM_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"TAGS_INHERIT" => array(
			"NAME" => GetMessage("SEARCH_TAGS_INHERIT"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
		),
	    "FONT_MAX" => array(
			"NAME" => GetMessage("SEARCH_FONT_MAX"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "50"
	    ),
	    "FONT_MIN" => array(
			"NAME" => GetMessage("SEARCH_FONT_MIN"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "10"
	    ),
	    "COLOR_NEW" => array(
			"NAME" => GetMessage("SEARCH_COLOR_NEW"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "000000"
	    ),
	    "COLOR_OLD" => array(
			"NAME" => GetMessage("SEARCH_COLOR_OLD"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "C8C8C8"
	    ),
	    "PERIOD_NEW_TAGS" => array(
			"NAME" => GetMessage("SEARCH_PERIOD_NEW_TAGS"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => ""
	    ),
	    "SHOW_CHAIN" => array(
			"NAME" => GetMessage("SEARCH_SHOW_CHAIN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
	    ),
		"COLOR_TYPE" => array(
			"NAME" => GetMessage("SEARCH_COLOR_TYPE"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
		),
	    "WIDTH" => array(
			"NAME" => GetMessage("SEARCH_WIDTH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "100%"),
	    "CELL_COUNT" => array(
			"NAME" => GetMessage("SEARCH_CELL_COUNT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "0"),
);
?>