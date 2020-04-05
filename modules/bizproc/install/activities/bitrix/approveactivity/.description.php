<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPAA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPAA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "ApproveActivity",
	"JSCLASS" => "ApproveActivity",
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
		"VotedCount" => array(
			"NAME" => GetMessage("BPAA_DESCR_VC"),
			"TYPE" => "int",
		),
		"TotalCount" => array(
			"NAME" => GetMessage("BPAA_DESCR_TC"),
			"TYPE" => "int",
		),
		"VotedPercent" => array(
			"NAME" => GetMessage("BPAA_DESCR_VP"),
			"TYPE" => "int",
		),
		"ApprovedPercent" => array(
			"NAME" => GetMessage("BPAA_DESCR_AP"),
			"TYPE" => "int",
		),
		"NotApprovedPercent" => array(
			"NAME" => GetMessage("BPAA_DESCR_NAP"),
			"TYPE" => "int",
		),
		"ApprovedCount" => array(
			"NAME" => GetMessage("BPAA_DESCR_AC"),
			"TYPE" => "int",
		),
		"NotApprovedCount" => array(
			"NAME" => GetMessage("BPAA_DESCR_NAC"),
			"TYPE" => "int",
		),
		"LastApprover" => array(
			"NAME" => GetMessage("BPAA_DESCR_LA"),
			"TYPE" => "user",
		),
		"LastApproverComment" => array(
			"NAME" => GetMessage("BPAA_DESCR_LA_COMMENT"),
			"TYPE" => "string",
		),
		"UserApprovers" => array(
			"NAME" => GetMessage("BPAA_DESCR_APPROVERS"),
			"TYPE" => "user",
		),
		"Approvers" => array(
			"NAME" => GetMessage("BPAA_DESCR_APPROVERS_STRING"),
			"TYPE" => "string",
		),
		"UserRejecters" => array(
			"NAME" => GetMessage("BPAA_DESCR_REJECTERS"),
			"TYPE" => "user",
		),
		"Rejecters" => array(
			"NAME" => GetMessage("BPAA_DESCR_REJECTERS_STRING"),
			"TYPE" => "string",
		),
		"IsTimeout" => array(
			"NAME" => GetMessage("BPAA_DESCR_TA1"),
			"TYPE" => "int",
		),
	),
);