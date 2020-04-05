<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IBLOCK_ELEMENT_ADD_NAME"),
	"DESCRIPTION" => GetMessage("IBLOCK_ELEMENT_ADD_DESCRIPTION"),
	"ICON" => "/images/eadd.gif",
	"COMPLEX" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "iblock_element_add",
			"NAME" => GetMessage("T_IBLOCK_DESC_ELEMENT_ADD"),
			"CHILD" => array(
				"ID" => "element_add_cmpx",
			),
		),
	),
);
?>