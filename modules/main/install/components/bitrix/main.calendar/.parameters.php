<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"SHOW_INPUT" => array(
			"NAME" => GetMessage("COMP_MAIN_CALENDAR_SHOW_INPUT"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"Y" => GetMessage("COMP_MAIN_CALENDAR_SHOW_INPUT_Y"),
				"N" => GetMessage("COMP_MAIN_CALENDAR_SHOW_INPUT_N"),
			),
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),

		"FORM_NAME" => array(
			"NAME" => GetMessage("COMP_MAIN_CALENDAR_FORM_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),

		"INPUT_NAME" => array(
			"NAME" => GetMessage("COMP_MAIN_CALENDAR_INPUT_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "date_fld",
			"PARENT" => "BASE",
		),

		"INPUT_NAME_FINISH" => array(
			"NAME" => GetMessage("COMP_MAIN_CALENDAR_INPUT_NAME_FINISH"),
			"TYPE" => "STRING",
			"DEFAULT" => "date_fld_finish",
			"PARENT" => "BASE",
		),

		"INPUT_VALUE" => array(
			"NAME" => GetMessage("COMP_MAIN_CALENDAR_INPUT_VALUE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),

		"INPUT_VALUE_FINISH" => array(
			"NAME" => GetMessage("COMP_MAIN_CALENDAR_INPUT_VALUE_FINISH"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),

		"SHOW_TIME" => array(
			"NAME" => GetMessage("COMP_MAIN_CALENDAR_SHOW_TIME"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "BASE",
		),

		"HIDE_TIMEBAR" => array(
			"NAME" => GetMessage("COMP_MAIN_CALENDAR_HIDE_TIMEBAR"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "BASE"
		),
	),
);
?>