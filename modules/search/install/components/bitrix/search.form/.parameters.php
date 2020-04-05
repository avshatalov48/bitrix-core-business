<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"PAGE" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("SEARCH_FORM_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => "#SITE_DIR#search/index.php",
		),
	),
);
?>
