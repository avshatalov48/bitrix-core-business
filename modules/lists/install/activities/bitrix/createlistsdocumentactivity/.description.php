<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	'NAME' => GetMessage('BPCLDA_DESCR_NAME'),
	'DESCRIPTION' => GetMessage('BPCLDA_DESCR_DESCR'),
	'TYPE' => 'activity',
	'CLASS' => 'CreateListsDocumentActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => array(
		'ID' => 'document',
	),
	"RETURN" => array(
		"ElementId" => array(
			"NAME" => 'Id',
			"TYPE" => "int",
		),
	)
);