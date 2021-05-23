<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage('HLLIST_COMPONENT_NAME'),
	"DESCRIPTION" => GetMessage('HLLIST_COMPONENT_DESCRIPTION'),
	"ICON" => "images/hl_list.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 10,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "hlblock",
			"NAME" => GetMessage('HLLIST_COMPONENT_CATEGORY_TITLE'),
			"CHILD" => array(
				"ID" => "hlblock_list",
			),
		),
	),
);

?>