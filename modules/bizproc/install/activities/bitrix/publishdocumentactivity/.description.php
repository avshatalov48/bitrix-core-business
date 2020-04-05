<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPPDA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPPDA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "PublishDocumentActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "document",
	),
	'FILTER' => array(
		'EXCLUDE' => array(
			array('crm'),
		)
	)
);
?>
