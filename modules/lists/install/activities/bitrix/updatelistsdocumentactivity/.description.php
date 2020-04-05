<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = [
	'NAME'           => GetMessage('BPULDA_DESCR_NAME'),
	'DESCRIPTION'    => GetMessage('BPULDA_DESCR_DESCR'),
	'TYPE'           => ['activity', 'robot_activity'],
	'CLASS'          => 'UpdateListsDocumentActivity',
	'JSCLASS'        => 'BizProcActivity',
	'CATEGORY'       => [
		'ID' => 'document',
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee'
	]
];