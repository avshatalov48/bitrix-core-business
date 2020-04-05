<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPSHA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPSHA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "SaveHistoryActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "document",
	),
	'FILTER' => array(
		'INCLUDE' => array(
			['iblock']
		)
	)
);