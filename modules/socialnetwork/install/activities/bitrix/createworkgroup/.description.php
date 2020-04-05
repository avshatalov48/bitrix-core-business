<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPCWG_DESCR_NAME2"),
	"DESCRIPTION" => GetMessage("BPCWG_DESCR_DESCR2"),
	"TYPE" => "activity",
	"CLASS" => "CreateWorkGroup",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "other",
	),
	"RETURN" => array(
		"GroupId" => array(
			"NAME" => GetMessage("BPCWG_GROUP_ID"),
			"TYPE" => "int",
		),
	),
);
?>