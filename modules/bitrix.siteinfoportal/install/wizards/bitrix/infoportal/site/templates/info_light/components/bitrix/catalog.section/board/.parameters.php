<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arTemplateParameters = array(
	"TREE_LINE_ELEMENT_COUNT" => Array(
		"NAME" => GetMessage("IBLOCK_LINE_ELEMENT_COUNT"),
		"TYPE" => "TEXT",
		"DEFAULT" => "2",
	),
	"TREE_DETAIL_PAGE_URL" => Array(
		"NAME" => GetMessage("IBLOCK_DETAIL_PAGE_URL"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
);
?>