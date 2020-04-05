<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPSFA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPSFA_DESCR_DESCR"),
	"TYPE" => array("activity", "robot_activity"),
	"CLASS" => "SetFieldActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "document",
	),
	'ROBOT_SETTINGS' => array(
		'TITLE' => GetMessage('BPSFA_DESCR_ROBOT_TITLE'),
		'CATEGORY' => 'employee'
	)
);