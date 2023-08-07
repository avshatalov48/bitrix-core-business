<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"GROUPS" => array(
		"SITE_MAP_PARAMS" => array(
			"NAME" => GetMessage("MAIN_SITE_MAP_PARAMS_NAME"),
		),
	),
	
	"PARAMETERS" => array(
		"LEVEL" => array(
			"NAME" => GetMessage("COMP_MAIN_SITE_MAP_LEVEL_NAME"), 
			"TYPE" => "STRING",
			"DEFAULT" => "3",
			"PARENT" => "SITE_MAP_PARAMS",
		),

		"COL_NUM" => array(
			"NAME" => GetMessage("COMP_MAIN_SITE_MAP_COL_NUM_NAME"), 
			"TYPE" => "STRING",
			"DEFAULT" => "1",
			"PARENT" => "SITE_MAP_PARAMS",			
		),

		"SHOW_DESCRIPTION" => array(
			"NAME" => GetMessage("COMP_MAIN_SITE_MAP_SHOW_DESCRIPTION"), 
			"TYPE" => "CHECKBOX",
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "N",	
			"PARENT" => "SITE_MAP_PARAMS",
		),
		
		
		"SET_TITLE" => array(),
		"CACHE_TIME" => array("DEFAULT" => "3600"),
	),
);
?>