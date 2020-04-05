<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPRIA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPRIA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "RequestInformationActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "document",
		'OWN_ID' => 'task',
		'OWN_NAME' => GetMessage('BPAA_DESCR_TASKS')
	),
	"RETURN" => array(
		'TaskId' => [
			'NAME' => 'ID',
			'TYPE' => 'int'
		],
		"Comments" => array(
			"NAME" => GetMessage("BPAA_DESCR_CM"),
			"TYPE" => "string",
		),
		"IsTimeout" => array(
			"NAME" => GetMessage("BPAA_DESCR_TA1"),
			"TYPE" => "int",
		),
		"InfoUser" => array(
			"NAME" => GetMessage("BPAA_DESCR_LU"),
			"TYPE" => "user",
		),
		"Changes" => array(
			"NAME" => GetMessage("BPAA_DESCR_CHANGES"),
			"TYPE" => "string",
		),
	),
);