<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"GROUPS" => array(
		"VARIABLE_ALIASES" => array(
			"NAME" => GetMessage("B_VARIABLE_ALIASES"),
		),
	),
	"PARAMETERS" => Array(
		"GROUPS_COUNT" => Array(
				"NAME" => GetMessage("BLOG_DESCR_GROUP_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 0,
				"PARENT" => "VISUAL",
			),
		"COLS_COUNT" => Array(
				"NAME" => GetMessage("BLOG_DESCR_COLS_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 3,
				"PARENT" => "VISUAL",
			),
		"SORT_BY1" => array(
			"NAME"				=> GetMessage("BLOG_DESCR_SORT_1"),
			"TYPE"				=> "LIST",
			"VALUES"			=> array(
				"ID"			=> "ID",
				"NAME"			=> GetMessage("BLOG_DESCR_GROUP_NAME"),
				),
			"ADDITIONAL_VALUES"	=> "Y",
			"DEFAULT"			=> "NAME",
			"PARENT" => "ADDITIONAL_SETTINGS",
			),
		"SORT_ORDER1" => array(
			"NAME"				=> GetMessage("BLOG_DESCR_SORT_ORDER"),
			"TYPE"				=> "LIST",
			"VALUES"			=> array("ASC" => GetMessage("BLOG_DESCR_SORT_ASC"), "DESC" => GetMessage("BLOG_DESCR_SORT_DESC")),
			"ADDITIONAL_VALUES"	=> "N",
			"DEFAULT"			=> "ASC",
			"PARENT" => "ADDITIONAL_SETTINGS",
			),
		"SORT_BY2" => array(
			"NAME"				=> GetMessage("BLOG_DESCR_SORT_2"),
			"TYPE"				=> "LIST",
			"VALUES"			=> array(
				"ID"			=> "ID",
				"NAME"			=> GetMessage("BLOG_DESCR_GROUP_NAME"),
				),
			"ADDITIONAL_VALUES"	=> "Y",
			"DEFAULT"			=> "ID",
			"PARENT" => "ADDITIONAL_SETTINGS",
			),
		"SORT_ORDER2" => array(
			"NAME"				=> GetMessage("BLOG_DESCR_SORT_ORDER"),
			"TYPE"				=> "LIST",
			"VALUES"			=> array("ASC" => GetMessage("BLOG_DESCR_SORT_ASC"), "DESC" => GetMessage("BLOG_DESCR_SORT_DESC")),
			"ADDITIONAL_VALUES"	=> "N",
			"DEFAULT"			=> "ASC",
			"PARENT" => "ADDITIONAL_SETTINGS",
			),
		"PATH_TO_GROUP" => Array(
			"NAME" => GetMessage("BMG_PATH_TO_GROUP"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"GROUP_VAR" => Array(
			"NAME" => GetMessage("BMG_GROUP_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PAGE_VAR" => Array(
			"NAME" => GetMessage("BMG_PAGE_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"CACHE_TIME"	=>	array("DEFAULT"=>"86400"),
	)
);
?>