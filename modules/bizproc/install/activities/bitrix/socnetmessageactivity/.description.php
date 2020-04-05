<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPSNMA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPSNMA_DESCR_DESCR"),
	"TYPE" => array('activity', 'robot_activity'),
	"CLASS" => "SocNetMessageActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
	'ROBOT_SETTINGS' => array(
		'CATEGORY' => 'employee',
		'TITLE' => GetMessage('BPSNMA_DESCR_ROBOT_TITLE'),
		'RESPONSIBLE_PROPERTY' => 'MessageUserTo'
	),
);