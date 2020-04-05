<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"SEF_MODE" => Array(
			"list" => Array(
				"NAME" => GetMessage("SPP_LIST_DESC"),
				"DEFAULT" => "profile_list.php",
				"VARIABLES" => array()
			),
			"detail" => Array(
				"NAME" => GetMessage("SPP_DETAIL_DESC"),
				"DEFAULT" => "profile_detail.php?ID=#ID#",
				"VARIABLES" => array("ID")
			),
		),

		"PER_PAGE" => Array(
			"NAME" => GetMessage("SPP_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "20",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		
		'USE_AJAX_LOCATIONS' => array(
			'NAME' => GetMessage("SPP_USE_AJAX_LOCATIONS"),
			'TYPE' => 'CHECKBOX',
			'MULTIPLE' => 'N',
			'DEFAULT' => 'N',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"SET_TITLE" => Array(),

	)
);
?>
