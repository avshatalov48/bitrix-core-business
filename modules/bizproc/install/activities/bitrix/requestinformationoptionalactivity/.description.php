<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPRIOA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPRIOA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "RequestInformationOptionalActivity",
	"JSCLASS" => "RequestInformationOptionalActivity",
	"CATEGORY" => array(
		"ID" => "document",
		'OWN_ID' => 'task',
		'OWN_NAME' => GetMessage('BPRIOA_DESCR_TASKS')
	),
	"RETURN" => array(
		'TaskId' => [
			'NAME' => 'ID',
			'TYPE' => 'int'
		],
		"Comments" => array(
			"NAME" => GetMessage("BPRIOA_DESCR_CM"),
			"TYPE" => "string",
		),
		"IsTimeout" => array(
			"NAME" => GetMessage("BPRIOA_DESCR_TA1"),
			"TYPE" => "int",
		),
		"InfoUser" => array(
			"NAME" => GetMessage("BPRIOA_DESCR_LU"),
			"TYPE" => "user",
		),
	),
);