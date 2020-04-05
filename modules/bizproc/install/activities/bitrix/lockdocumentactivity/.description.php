<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPLDA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPLDA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "LockDocumentActivity",
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