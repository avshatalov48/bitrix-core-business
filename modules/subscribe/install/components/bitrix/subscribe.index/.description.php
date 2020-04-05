<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SUBSCR_SUBCRIBE_NAME"),
	"DESCRIPTION" => GetMessage("SUBSCR_SUBCRIBE_DESC"),
	"ICON" => "/images/subscr_rubrics.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "service",
		//"NAME" => GetMessage("MAIN_SERVICES"),
		"CHILD" => array(
			"ID" => "subscribe",
			"NAME" => GetMessage("SUBSCR_SERVICE")
		)
	),
);

?>