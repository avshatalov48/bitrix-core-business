<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("STAT_TABLE_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("STAT_TABLE_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/stat_table.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "utility",
		"CHILD" => array(
			"ID" => "statistic",
			"NAME" => GetMessage("STAT_TEMPLATE_SECTION_NAME")
		)
	),
);

?>