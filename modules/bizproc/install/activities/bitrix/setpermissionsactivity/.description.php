<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPSPA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPSPA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "SetPermissionsActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "other",
	),
	'FILTER' => array(
		'EXCLUDE' => array(
			array('crm'),
			array('disk'),
		)
	)
);
?>
