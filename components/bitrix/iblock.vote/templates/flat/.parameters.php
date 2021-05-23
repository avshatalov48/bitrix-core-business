<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arTemplateParameters = array(
	"DISPLAY_AS_RATING" => Array(
		"NAME" => GetMessage("TP_BIV_DISPLAY_AS_RATING"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"rating" => GetMessage("TP_BIV_RATING"),
			"vote_avg" => GetMessage("TP_BIV_AVERAGE"),
		),
		"DEFAULT" => "rating",
	),
	"SHOW_RATING" => Array(
		"NAME" => GetMessage("TP_BIV_SHOW_RATING"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	),
);
?>
