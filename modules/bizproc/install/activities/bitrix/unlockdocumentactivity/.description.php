<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPUDA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPUDA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "UnlockDocumentActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "document",
	),
	'FILTER' => array(
		'EXCLUDE' => array(
			['disk'],
			['crm']
		)
	)
);