<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_RO_NAME"),
	"DESCRIPTION" => GetMessage("CD_RO_DESCRIPTION"),
	"ICON" => "/images/rss_out.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "rss",
			"NAME" => GetMessage("CD_RO_RSS")
		)
	),
);

?>