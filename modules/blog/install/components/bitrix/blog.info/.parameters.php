<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_BLOG", 0, LANGUAGE_ID);
$blogProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$blogProp[$val["FIELD_NAME"]] = (strLen($val["EDIT_FORM_LABEL"]) > 0 ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$arComponentParameters = Array(
	"GROUPS" => array(
		"VARIABLE_ALIASES" => array(
			"NAME" => GetMessage("B_VARIABLE_ALIASES"),
		),
	),
	"PARAMETERS" => Array(
		"PATH_TO_BLOG" => Array(
			"NAME" => GetMessage("BI_PATH_TO_BLOG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_BLOG_CATEGORY" => Array(
			"NAME" => GetMessage("BI_PATH_TO_BLOG_CATEGORY"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER" => Array(
			"NAME" => GetMessage("BI_PATH_TO_USER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),

		"BLOG_VAR" => Array(
			"NAME" => GetMessage("BI_BLOG_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"USER_VAR" => Array(
			"NAME" => GetMessage("BI_USER_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PAGE_VAR" => Array(
			"NAME" => GetMessage("BI_PAGE_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"BLOG_URL" => Array(
			"NAME" => GetMessage("BI_BLOG_URL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$blog}",
			"COLS" => 25,
			"PARENT" => "DATA_SOURCE",
		),
		"CATEGORY_ID" => Array(
			"NAME" => GetMessage("BI_CATEGORY_ID"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$category}",
			"COLS" => 25,
			"PARENT" => "BASE",
		),
		"CACHE_TIME"	=>	array("DEFAULT"=>"86400"),
		
		"BLOG_PROPERTY_LIST"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("BLOG_PROPERTY_LIST"),
			"TYPE" => "LIST",
			"VALUES" => $blogProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
	)
);
?>