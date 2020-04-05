<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"SHOW_COUNT" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME"=>GetMessage("SUBSCR_SHOW_COUNT"),
			"TYPE"=>"CHECKBOX",
			"DEFAULT"=>"N",
		),
		"SHOW_HIDDEN" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME"=>GetMessage("SUBSCR_SHOW_HIDDEN"),
			"TYPE"=>"CHECKBOX",
			"DEFAULT"=>"N",
		),
		"PAGE" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME"=>GetMessage("SUBSCR_FORM_PAGE"),
			"TYPE"=>"STRING",
			"DEFAULT"=>COption::GetOptionString("subscribe", "subscribe_section")."subscr_edit.php",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
		"SET_TITLE" => array(),
	),
);
?>
