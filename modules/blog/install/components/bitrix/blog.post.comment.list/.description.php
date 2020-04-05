<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("BPC_DEFAULT_TEMPLATE_NAME__LIST"),
	"DESCRIPTION" => GetMessage("BPC_DEFAULT_TEMPLATE__LIST_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"SORT" => 180,
	"PATH" => array(
		"ID" => "communication",
		"CHILD" => array(
			"ID" => "blog",
			"NAME" => GetMessage("BPC_NAME")
		)
	),
);
?>