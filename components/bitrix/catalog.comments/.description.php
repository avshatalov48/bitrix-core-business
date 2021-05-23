<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("T_IBLOCK_DESC_CSC_LIST"),
	"DESCRIPTION" => GetMessage("T_IBLOCK_DESC_CSC_DESC"),
	"ICON" => "/images/like_buttons.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 10,
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