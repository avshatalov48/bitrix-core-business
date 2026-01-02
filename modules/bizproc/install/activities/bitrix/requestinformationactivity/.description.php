<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
	"NAME" => GetMessage("BPRIA_DESCR_NAME_1"),
	"DESCRIPTION" => GetMessage("BPRIA_DESCR_DESCR_1"),
	"TYPE" => "activity",
	"CLASS" => "RequestInformationActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "document",
		'OWN_ID' => 'task',
		'OWN_NAME' => GetMessage('BPAA_DESCR_TASKS')
	),
	'RETURN' => [
		'TaskId' => [
			'NAME' => 'ID',
			'TYPE' => 'int'
		],
		'Comments' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_CM_1'),
			'TYPE' => 'string',
		],
		'IsTimeout' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_TA1'),
			'TYPE' => 'int',
		],
		'InfoUser' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_LU'),
			'TYPE' => 'user',
		],
		'Changes' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_CHANGES'),
			'TYPE' => 'string',
		],
	],
);