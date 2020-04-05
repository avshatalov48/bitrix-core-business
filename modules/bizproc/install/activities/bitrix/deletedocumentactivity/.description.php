<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPDDA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPDDA_DESCR_DESCR"),
	"TYPE" => array("activity", "robot_activity"),
	"CLASS" => "DeleteDocumentActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "document",
	),
	'ROBOT_SETTINGS' => array(
		'CATEGORY' => 'employee',
		'TITLE' => GetMessage('BPDDA_DESCR_ROBOT_TITLE'),
		'IS_AUTO' => true
	),
);