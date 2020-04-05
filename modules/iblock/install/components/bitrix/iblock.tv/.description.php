<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("BITRIXTVBIG_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("BITRIXTVBIG_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/bitrix_tv.gif",
	"COMPLEX" => "N",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "media",
			"NAME" => GetMessage("BITRIXTVBIG_COMPONENTS"),
		),
	),
);
?>