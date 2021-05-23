<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BCI1_NAME"),
	"DESCRIPTION" => GetMessage("CD_BCI1_DESCRIPTION"),
	"ICON" => "/images/1c-imp.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 120,
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "",
			"NAME" => GetMessage("CD_BCI1_CATALOG"),
		),
	),
);

?>