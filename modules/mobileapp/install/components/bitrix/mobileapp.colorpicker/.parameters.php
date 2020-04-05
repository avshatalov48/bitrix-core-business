<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"SHOW_BUTTON" => array(
			"NAME" => GetMessage("COMP_MAIN_COLORPICKER_SHOW_BUTTON"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),

		"ID" => array(
			"NAME" => GetMessage("COMP_MAIN_COLORPICKER_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),

		"NAME" => array(
			"NAME" => GetMessage("COMP_MAIN_COLORPICKER_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage('COMP_MAIN_COLORPICKER_NAME_DEFAULT'),
			"PARENT" => "BASE",
		),

		"ONSELECT" => array(
			"NAME" => GetMessage("COMP_MAIN_COLORPICKER_ONSELECT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
	),
);
?>