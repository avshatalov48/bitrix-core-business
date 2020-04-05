<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("T_IBLOCK_DESC_RSS_SHOW"),
	"DESCRIPTION" => GetMessage("T_IBLOCK_DESC_RSS_SHOW_DESC"),
	"ICON" => "/images/rss_in.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "rss",
			"NAME" => GetMessage("T_IBLOCK_DESC_RSS")
		)
	),
);

?>