<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"INPUT_ID" => array(
			"NAME" => GetMessage("COMP_CLOCK_INPUT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"INPUT_NAME" => array(
			"NAME" => GetMessage("COMP_CLOCK_INPUT_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"INPUT_TITLE" => array(
			"NAME" => GetMessage("COMP_CLOCK_INPUT_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"INIT_TIME" => array(
			"NAME" => GetMessage("COMP_CLOCK_INIT_TIME"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"STEP" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("COMP_CLOCK_STEP"),
			"TYPE" => "LIST",
			"VALUES" => array(5, 15, 30, 60),
			"DEFAULT" => 5
		)
	)
);
?>