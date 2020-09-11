<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = [
	"NAME" => GetMessage("BPGUIA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPGUIA_DESCR_DESCR"),
	"TYPE" => ["activity", 'robot_activity'],
	"CLASS" => "GetUserInfoActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => [
		"ID" => "other",
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee'
	],
	'ADDITIONAL_RESULT' => ['UserFields'],
];