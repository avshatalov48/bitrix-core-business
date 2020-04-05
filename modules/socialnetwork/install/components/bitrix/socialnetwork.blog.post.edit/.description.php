<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("BPE_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("BPE_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"SORT" => 280,
	"PATH" => array(
		"ID" => "communication",
		"CHILD" => array(
			"ID" => "socialnetwork",
			"NAME" => GetMessage("BPE_NAME")
		)
	),
);
?>