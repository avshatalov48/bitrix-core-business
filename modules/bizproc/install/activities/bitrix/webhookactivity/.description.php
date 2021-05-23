<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPWHA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPWHA_DESCR_DESCR"),
	"TYPE" => array('activity', 'robot_activity'),
	"CLASS" => "WebHookActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "other",
	),
	'FILTER' => array(
		'EXCLUDE' => array(
			['crm', 'Bitrix\Crm\Integration\BizProc\Document\Dynamic'],
			['crm', 'Bitrix\Crm\Integration\BizProc\Document\Quote'],
		),
	),
	"ROBOT_SETTINGS" => array(
		'CATEGORY' => 'other'
	),
);