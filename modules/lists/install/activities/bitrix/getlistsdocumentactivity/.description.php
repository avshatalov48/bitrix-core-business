<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = [
	'NAME'              => GetMessage('BPGLDA_DESCR_NAME'),
	'DESCRIPTION'       => GetMessage('BPGLDA_DESCR_DESCR'),
	'TYPE'              => ['activity', 'robot_activity'],
	'CLASS'             => 'GetListsDocumentActivity',
	'JSCLASS'           => 'BizProcActivity',
	'CATEGORY'          => [
		'ID' => 'document',
	],
	'ADDITIONAL_RESULT' => ['FieldsMap'],
	'ROBOT_SETTINGS'    => [
		'CATEGORY' => 'employee'
	]
];