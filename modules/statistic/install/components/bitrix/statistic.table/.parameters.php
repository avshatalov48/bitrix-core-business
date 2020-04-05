<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"CACHE_TIME"  =>  Array("DEFAULT"=>20),
		"CACHE_FOR_ADMIN" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("C_STAT_CACHE_FOR_ADMIN"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
	),
);
?>
