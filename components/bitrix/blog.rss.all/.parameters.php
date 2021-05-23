<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"GROUPS" => array(
		"VARIABLE_ALIASES" => array(
			"NAME" => GetMessage("B_VARIABLE_ALIASES"),
		),
	),
	"PARAMETERS" => Array(
		"MESSAGE_COUNT" => Array(
				"NAME" => GetMessage("BR_NUM_POSTS"),
				"TYPE" => "STRING",
				"DEFAULT" => 10,
				"COLS" => 5,
				"PARENT" => "VISUAL",
			),
		"PATH_TO_POST" => Array(
			"NAME" => GetMessage("BR_PATH_TO_POST"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER" => Array(
			"NAME" => GetMessage("BR_PATH_TO_USER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),

		"BLOG_VAR" => Array(
			"NAME" => GetMessage("BR_BLOG_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"POST_VAR" => Array(
			"NAME" => GetMessage("BR_POST_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"USER_VAR" => Array(
			"NAME" => GetMessage("BR_USER_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PAGE_VAR" => Array(
			"NAME" => GetMessage("BR_PAGE_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"GROUP_ID" => Array(
			"NAME" => GetMessage("BR_GROUP_ID"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "DATA_SOURCE",
		),
		"TYPE" => Array(
			"NAME" => GetMessage("BR_TYPE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$type}",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => Array("rss1"=>"RSS .92", "rss2" => "RSS 2.0", "atom"=>"Atom .03"),
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"CACHE_TIME" => array("DEFAULT"=>"86400"),
	)
);
?>