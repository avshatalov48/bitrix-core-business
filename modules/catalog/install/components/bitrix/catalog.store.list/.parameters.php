<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"PHONE" => array(
			"NAME" => GetMessage("SHOW_PHONE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => 'N',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SCHEDULE" => array(
			"NAME" => GetMessage("SHOW_SCHEDULE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => 'N',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_ELEMENT" => array(
			'PARENT' => 'STORE_SETTINGS',
			'NAME' => GetMessage('STORE_PATH_EXT'),
			"TYPE"		=> "STRING",
			"DEFAULT"	=> "store/#store_id#",
		),
		"MAP_TYPE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("MAP_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array("Yandex","Google"),
			"DEFAULT" => "0",
		),
		"SET_TITLE" => array(),
		"CACHE_TIME" => array("DEFAULT"=>36000000),
		"TITLE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage('BX_CATALOG_CSL_TITLE'),
			"TYPE" => "STRING",
			"DEFAULT_VALUE" => ""
		)
	)
);