<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("ESHOP_LINKS_TITLE"),
	"DESCRIPTION" => GetMessage("ESHOP_LINKS_DESCR"),
	"ICON" => "/images/user_authform.gif",
	"PATH" => array(
		"ID" => GetMessage("T_ESHOP"),
		"CHILD" => array(
			"ID" => "eshop-socent-links",
			"NAME" => GetMessage("ESHOP_LINKS_TITLE")
		)
	),
);
?>