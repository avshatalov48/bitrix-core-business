<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arTemplateParameters = array(
	"SEPARATE" => array(
		"NAME" => GetMessage("F_SEPARATE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("F_IN_FORUM")),
	"SHOW_COLUMNS" => array(
		"NAME" => GetMessage("F_SHOW_COLUMNS"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => array(
			"USER_START_NAME" => GetMessage("F_SHOW_COLUMNS_USER_START_NAME"),
			"POSTS" => GetMessage("F_SHOW_COLUMNS_POSTS"),
			"VIEWS" => GetMessage("F_SHOW_COLUMNS_VIEWS"),
			"LAST_POST_DATE" => GetMessage("F_SHOW_COLUMNS_LAST_POST_DATE")),
		"DEFAULT" => array()),
	"SHOW_SORTING" => array(
		"NAME" => GetMessage("F_SHOW_SORTING"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N")
);

if ($arCurrentValues["SET_NAVIGATION"] == "Y")
	$arTemplateParameters["SHOW_NAV"] = array(
			"NAME" => GetMessage("F_SHOW_NAV"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"PARENT" => "PAGER_SETTINGS",
			"VALUES" => array(
				"TOP" => GetMessage("F_SHOW_NAV_TOP"),
				"BOTTOM" => GetMessage("F_SHOW_NAV_BOTTOM")),
			"DEFAULT" => array("BOTTOM"));
?>