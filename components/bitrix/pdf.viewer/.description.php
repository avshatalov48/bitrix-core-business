<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("FILEMAN_PDFVIEWER_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("FILEMAN_PDFVIEWER_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"COMPLEX" => "N",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "media",
			"NAME" => GetMessage("MEDIA")
		)
	),
);
?>