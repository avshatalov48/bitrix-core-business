<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = array(
	"NAME" => GetMessage("BPRIOA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPRIOA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "RequestInformationOptionalActivity",
	"JSCLASS" => "RequestInformationOptionalActivity",
	"CATEGORY" => array(
		"ID" => "task",
	),
	'RETURN' => [
		'TaskId' => [
			'NAME' => 'ID',
			'TYPE' => 'int'
		],
		'Comments' => [
			'NAME' => Loc::getMessage('BPRIOA_DESCR_CM_1'),
			'TYPE' => 'string',
		],
		'IsTimeout' => [
			'NAME' => Loc::getMessage('BPRIOA_DESCR_TA1'),
			'TYPE' => 'int',
		],
		'InfoUser' => [
			'NAME' => Loc::getMessage('BPRIOA_DESCR_LU'),
			'TYPE' => 'user',
		],
	],
);