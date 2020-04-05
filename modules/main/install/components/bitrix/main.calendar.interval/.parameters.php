<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"FORM_NAME" => array(
			"NAME" => GetMessage("COMP_INT_MAIN_CALENDAR_FORM_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"SELECT_NAME" => array(
			"NAME" => GetMessage("COMP_INT_MAIN_CALENDAR_SELECT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"SELECT_VALUE" => array(
			"NAME" => GetMessage("COMP_INT_MAIN_CALENDAR_SELECT_VAL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"INPUT_NAME_DAYS" => array(
			"NAME" => GetMessage("COMP_INT_MAIN_CALENDAR_INPUT_DAY"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"INPUT_VALUE_DAYS" => array(
			"NAME" => GetMessage("COMP_INT_MAIN_CALENDAR_INPUT_DAY_VAL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"INPUT_NAME_FROM" => array(
			"NAME" => GetMessage("COMP_INT_MAIN_CALENDAR_INPUT_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"INPUT_VALUE_FROM" => array(
			"NAME" => GetMessage("COMP_INT_MAIN_CALENDAR_INPUT_VALUE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"INPUT_NAME_TO" => array(
			"NAME" => GetMessage("COMP_INT_MAIN_CALENDAR_INPUT_NAME_FINISH"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"INPUT_VALUE_TO" => array(
			"NAME" => GetMessage("COMP_INT_MAIN_CALENDAR_INPUT_VALUE_FINISH"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
	),
);
