<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPAR_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPAR_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "ReviewActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "document",
		'OWN_ID' => 'task',
		'OWN_NAME' => GetMessage('BPAR_DESCR_TASKS')
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
		"ReviewedCount" => array(
			"NAME" => GetMessage("BPAR_DESCR_RC"),
			"TYPE" => "int",
		),
		"TotalCount" => array(
			"NAME" => GetMessage("BPAR_DESCR_TC"),
			"TYPE" => "int",
		),
		"IsTimeout" => array(
			"NAME" => GetMessage("BPAR_DESCR_TA1"),
			"TYPE" => "int",
		),
		"LastReviewer" => array(
			"NAME" => GetMessage("BPAR_DESCR_LR"),
			"TYPE" => "user",
		),
		"LastReviewerComment" => array(
			"NAME" => GetMessage("BPAR_DESCR_LR_COMMENT"),
			"TYPE" => "string",
		),
	),
);