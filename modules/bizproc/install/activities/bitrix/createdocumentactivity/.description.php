<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arActivityDescription = [
	"NAME" => GetMessage("BPCDA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPCDA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "CreateDocumentActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => [
		"ID" => "document",
	],
	'RETURN' => [
		'ErrorMessage' => [
			'NAME' => GetMessage('BPCDA_DESCR_ERROR_MESSAGE'),
			'TYPE' => 'string',
		],
	],
	'FILTER' => [
		'EXCLUDE' => [
			['crm', 'Bitrix\Crm\Integration\BizProc\Document\Order'],
			['crm', 'Bitrix\Crm\Integration\BizProc\Document\Invoice'],
			['tasks'],
		],
	],
];
