<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"SEF_MODE" => array(
			"liststores" => array(
				"NAME" => GetMessage("CATALOG_SEF_INDEX"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array(),
			),
			"element" => array(
				"NAME" => GetMessage("CATALOG_SEF_DETAIL"),
				"DEFAULT" => "#store_id#",
				"VARIABLES" => array(),
			),
		),
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
		"SET_TITLE" => array(
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('USE_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),
		"TITLE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME"		=> GetMessage("TITLE"),
			"TYPE"		=> "STRING",
			"DEFAULT"	=> GetMessage('DEFAULT_TITLE'),
		),
		"MAP_TYPE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("MAP_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array("Yandex","Google"),
			"DEFAULT" => "0",
		),
		"CACHE_TIME" => array("DEFAULT"=>"3600"),
	)
);