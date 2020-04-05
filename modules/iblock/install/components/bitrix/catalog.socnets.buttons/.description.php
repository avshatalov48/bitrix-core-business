<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IBLOCK_ELEMENT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("IBLOCK_ELEMENT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/like_buttons.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 20,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "content-social",
			"NAME" => GetMessage("CP_CONTENT_SOCIAL_PARENT_TITLE"),
			"SORT" => 300,
		)
	)
);
?>