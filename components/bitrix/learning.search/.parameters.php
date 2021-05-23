<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("learning"))
	return false;

$arComponentParameters = Array(
	"GROUPS" => array(
		"PAGER_SETTINGS" => array(
			"NAME" => GetMessage("SEARCH_PAGER_SETTINGS"),
		),
	),
	"PARAMETERS" => Array(
		"PAGE_RESULT_COUNT" => Array(
			"NAME" => GetMessage("LEARNING_PAGE_RESULT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => 10,
			"PARENT" => "VISUAL",
		),
		"DISPLAY_TOP_PAGER" => Array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("CP_BSP_DISPLAY_TOP_PAGER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"DISPLAY_BOTTOM_PAGER" => Array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("CP_BSP_DISPLAY_BOTTOM_PAGER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
	)
);
?>