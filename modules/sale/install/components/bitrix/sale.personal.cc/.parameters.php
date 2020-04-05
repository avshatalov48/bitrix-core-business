<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"SEF_MODE" => Array(
			"list" => Array(
				"NAME" => GetMessage("SPC_LIST_DESC"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array("ID")
			),
			"detail" => Array(
				"NAME" => GetMessage("SPC_DETAIL_DESC"),
				"DEFAULT" => "detail.php?ID=#ID#",
				"VARIABLES" => array("ID")
			),
		),

		"PER_PAGE" => Array(
			"NAME" => GetMessage("SPC_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "20",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"SET_TITLE" => Array(),

	)
);
?>
