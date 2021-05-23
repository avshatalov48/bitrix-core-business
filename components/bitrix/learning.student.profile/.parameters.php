<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"TRANSCRIPT_DETAIL_TEMPLATE" => Array(
			"NAME"=>GetMessage("LEARNING_TRANSCRIPT_TEMPLATE_NAME"), 
			"TYPE"=>"STRING", 
			"PARENT" => "URL_TEMPLATES",
			"COLS" => 45,
			"DEFAULT"=>"certification/?TRANSCRIPT_ID=#TRANSCRIPT_ID#"
		),
		"SET_TITLE" => Array(),
	)
);
?>