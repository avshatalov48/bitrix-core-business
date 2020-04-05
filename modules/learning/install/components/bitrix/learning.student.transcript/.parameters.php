<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"TRANSCRIPT_ID" => array(
			"NAME" => GetMessage("LEARNING_TRANSCRIPT_ID_NAME"),
			"TYPE"=>"STRING",
			"DEFAULT"=>'={$_REQUEST["TRANSCRIPT_ID"]}',
			"PARENT" => "BASE",
			"COLS" => 45
		),
		"SET_TITLE" => Array(),
		"NAME_TEMPLATE" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("LEARNING_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
	)
);
?>