<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"GROUPS" => array(
		"VARIABLE_ALIASES" => array(
			"NAME" => GetMessage("B_VARIABLE_ALIASES"),
		),
	),
	"PARAMETERS" => Array(
		"PATH_TO_RSS" => Array(
			"NAME" => GetMessage("BRL_PATH_TO_RSS"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_RSS_ALL" => Array(
			"NAME" => GetMessage("BRL_PATH_TO_RSS_ALL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"BLOG_VAR" => Array(
			"NAME" => GetMessage("BRL_BLOG_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PAGE_VAR" => Array(
			"NAME" => GetMessage("BRL_PAGE_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"GROUP_VAR" => Array(
			"NAME" => GetMessage("BRL_GROUP_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"BLOG_URL" => Array(
			"NAME" => GetMessage("BRL_BLOG_URL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "DATA_SOURCE",
		),
		"POST_ID" => Array(
			"NAME" => GetMessage("BRL_POST_ID"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$post_id}",
			"COLS" => 25,
			"PARENT" => "DATA_SOURCE",
		),
		"GROUP_ID" => Array(
			"NAME" => GetMessage("BRL_GROUP_ID"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "DATA_SOURCE",
		),
		"RSS1" => Array(
		  	"NAME" => GetMessage("BRL_RSS1"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "BASE",
		),
		"RSS2" => Array(
		  	"NAME" => GetMessage("BRL_RSS2"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "BASE",
		),
		"ATOM" => Array(
		  	"NAME" => GetMessage("BRL_ATOM"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "BASE",
		),
		"MODE" => Array(
		  	"NAME" => GetMessage("BRL_MODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => Array(
						"B" => GetMessage("BRL_P_B"), 
						"S" => GetMessage("BRL_P_S"),
						"G" => GetMessage("BRL_P_G"), 
						"C" => GetMessage("BRL_P_C"), 
					),
			"DEFAULT" =>"B",
			"PARENT" => "BASE",
		),
	)
);
?>