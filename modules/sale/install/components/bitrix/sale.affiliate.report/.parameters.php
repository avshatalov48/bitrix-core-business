<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arComponentParameters = array(
	"PARAMETERS" => array(
		"REGISTER_PAGE" => array(
			"NAME" => GetMessage("SPCD1_REGISTER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "register.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SET_TITLE" => array(),

	),
);
?>