<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPMA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPMA_DESCR_DESCR"),
	"TYPE" => array('activity', 'robot_activity'),
	"CLASS" => "MailActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
	'ROBOT_SETTINGS' => array(
		'CATEGORY' => 'employee',
		'TITLE' => GetMessage('BPMA_DESCR_ROBOT_TITLE'),
		'RESPONSIBLE_PROPERTY' => 'MailUserToArray'
	),
);