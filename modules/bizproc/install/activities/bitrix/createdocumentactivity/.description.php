<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPCDA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPCDA_DESCR_DESCR'),
	'TYPE' => 'activity',
	'CLASS' => 'CreateDocumentActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'document',
	],
	'RETURN' => [
		'ErrorMessage' => [
			'NAME' => Loc::getMessage('BPCDA_DESCR_ERROR_MESSAGE'),
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
