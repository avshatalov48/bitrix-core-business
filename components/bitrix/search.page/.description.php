<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SEARCH_SEARCH_PAGE_NAME"),
	"DESCRIPTION" => GetMessage("SEARCH_SEARCH_PAGE_DESCRIPTION"),
	"ICON" => "/images/search_page.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "utility",
		"CHILD" => array(
			"ID" => "search",
			"NAME" => GetMessage("SEARCH_SERVICE")
		)
	),
);

?>