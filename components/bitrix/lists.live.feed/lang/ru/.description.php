<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$arComponentDescription = array(
	"NAME"        => GetMessage("CD_BLL_NAME"),
	"DESCRIPTION" => GetMessage("CD_BLL_DESCRIPTION"),
	"ICON"        => "/images/lists_excel.gif",
	"SORT"        => 120,
	"CACHE_PATH"  => "Y",
	"PATH"        => array(
		"ID"    => "content",
		"CHILD" => array(
			"ID"   => "lists",
			"NAME" => GetMessage("CD_BLL_LISTS"),
			"SORT" => 35,
		)
	),
);