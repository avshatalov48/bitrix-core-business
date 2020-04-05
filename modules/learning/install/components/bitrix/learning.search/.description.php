<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LEARNING_SEARCH_COMPLEX_NAME"),
	"DESCRIPTION" => GetMessage("LEARNING_SEARCH_COMPLEX_DESC"),
	"ICON" => "/images/search.gif",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "learning",
			"NAME" => GetMessage("LEARNING_SERVICE"),
			"CHILD" => array(
				"ID" => "search",
				"NAME" => GetMessage("LEARNING_SEARCH_SERVICE")
			)
		)
	),
);
?>