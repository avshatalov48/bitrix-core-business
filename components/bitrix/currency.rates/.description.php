<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arComponentDescription = array(
	"NAME"			=> GetMessage("CURRENCY_SHOW_RATES_COMPONENT_NAME"),
	"DESCRIPTION"	=> GetMessage("CURRENCY_SHOW_RATES_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/currency_rates.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "CURRENCY",
			"NAME" => GetMessage("CURRENCY_GROUP_NAME"),
		),
	),
);
?>