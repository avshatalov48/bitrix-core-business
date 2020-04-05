<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPSWFA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPSWFA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "StartWorkflowActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "document",
	),
	"RETURN" => array(
		"WorkflowId" => array(
			"NAME" => GetMessage("BPSWFA_DESCR_WORKFLOW_ID"),
			"TYPE" => "string",
		),
	),
);
?>
