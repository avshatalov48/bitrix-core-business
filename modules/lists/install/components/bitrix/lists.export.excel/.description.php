<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$arComponentDescription = array(
	"NAME"        => GetMessage("CD_BLEE_NAME"),
	"DESCRIPTION" => GetMessage("CD_BLEE_DESCRIPTION"),
	"ICON"        => "/images/lists_excel.gif",
	"SORT"        => 120,
	"CACHE_PATH"  => "Y",
	"PATH"        => array(
		"ID"    => "content",
		"CHILD" => array(
			"ID"   => "lists",
			"NAME" => GetMessage("CD_BLEE_LISTS"),
			"SORT" => 35,
		)
	),
);