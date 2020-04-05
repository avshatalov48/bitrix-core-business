<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPRNDSA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPRNDSA_DESCR_DESCR"),
	"TYPE" => array('activity', 'robot_activity'),
	"CLASS" => "RandomStringActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "other",
	),
	'RETURN' => [
		'ResultString' => [
			'NAME' => GetMessage("BPRNDSA_DESCR_RESULT_STRING"),
			'TYPE' => 'string'
		]
	],
	"ROBOT_SETTINGS" => array(
		'CATEGORY' => 'employee'
	),
);