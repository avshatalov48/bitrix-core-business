<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"STORE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("STORE_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => ""
		),
		"MAP_TYPE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("MAP_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array("Yandex","Google"),
			"DEFAULT" => "0",
		),
		"CACHE_TIME" => array("DEFAULT"=>"3600"),
		"SET_TITLE" => array(),
	)
);