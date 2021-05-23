<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IBLOCK_VOTE_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("IBLOCK_VOTE_TEMPLATE_DESCRIPTION"),
	//"ICON" => "/images/photo_detail.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "iblock",
			"NAME" => GetMessage("T_IBLOCK_DESC_IBLOCK"),
		),
	),
);

?>