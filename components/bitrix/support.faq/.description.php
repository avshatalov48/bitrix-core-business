<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SUPPORT_FAQ_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("SUPPORT_FAQ_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/support.faq.gif",
	"COMPLEX" => "Y",
	"PATH" => array(
		"ID" => "service",
		"SORT" => 1000,
		"CHILD" => array(
			"ID" => "faq",
			"NAME" => GetMessage("SUPPORT_FAQ_COMPONENTS"),
			"SORT" => 30,
		),
	),
);

?>