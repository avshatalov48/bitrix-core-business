<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BCSE_NAME"),
	"DESCRIPTION" => GetMessage("CD_BCSE_DESCRIPTION"),
	"ICON" => "/images/cat_search.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 35,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog",
			"NAME" => GetMessage("CD_BCSE_CATALOG"),
			"SORT" => 30,
		)
	)
);
?>