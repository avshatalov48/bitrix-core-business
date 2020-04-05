<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentDescription = array(
	"NAME" => GetMessage("MAIN_PAGE_NAVIGATION_NAME"),
	"DESCRIPTION" => GetMessage("MAIN_PAGE_NAVIGATION_DESC"),
	"PATH" => array(
		"ID" => "utility",
		"CHILD" => array(
			"ID" => "navigation",
		),
	),
);
?>