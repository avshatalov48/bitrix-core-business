<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = [
	'NAME'           => GetMessage('BPCLDA_DESCR_NAME'),
	'DESCRIPTION'    => GetMessage('BPCLDA_DESCR_DESCR'),
	'TYPE'           => ['activity', 'robot_activity'],
	'CLASS'          => 'CreateListsDocumentActivity',
	'JSCLASS'        => 'BizProcActivity',
	'CATEGORY'       => [
		'ID' => 'document',
	],
	"RETURN"         => [
		"ElementId" => [
			"NAME" => 'Id',
			"TYPE" => "int",
		],
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee'
	]
];