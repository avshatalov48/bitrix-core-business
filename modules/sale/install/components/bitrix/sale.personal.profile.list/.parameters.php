<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"PATH_TO_DETAIL" => Array(
			"NAME" => GetMessage("SPPL_PATH_TO_DETAIL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PER_PAGE" => Array(
			"NAME" => GetMessage("SPPL_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => 20,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"SET_TITLE" => Array(),

	)
);
?>