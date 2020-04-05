<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPUPDA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPUPDA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "UnpublishDocumentActivity",
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
