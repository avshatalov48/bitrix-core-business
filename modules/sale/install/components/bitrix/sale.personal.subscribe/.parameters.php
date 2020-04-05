<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"SEF_MODE" => Array(
			"list" => Array(
				"NAME" => GetMessage("SPS_LIST_DESC"),
				"DEFAULT" => "subscribe_list.php",
				"VARIABLES" => array()
			),
			"cancel" => Array(
				"NAME" => GetMessage("SPS_CANCEL_DESC"),
				"DEFAULT" => "subscribe_cancel.php?ID=#ID#",
				"VARIABLES" => array("ID")
			),
		),

		"PER_PAGE" => Array(
			"NAME" => GetMessage("SPS_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "20",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"SET_TITLE" => Array(),

	)
);
?>
