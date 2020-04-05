<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arComponentDescription = array(
	"NAME" => GetMessage("SPCD1_REPORT"),
	"DESCRIPTION" => GetMessage("SPCD1_MONEYS"),
	"ICON" => "/images/icon.gif",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "affiliate",
			"NAME" => GetMessage("SPCD1_AFFILIATE")
		)
	),
);
?>