<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPAR_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPAR_DESCR_DESCR'),
	'TYPE' => 'activity',
	'CLASS' => 'ReviewActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'document',
		'OWN_ID' => 'task',
		'OWN_NAME' => Loc::getMessage('BPAR_DESCR_TASKS')
	],
	'RETURN' => [
		'TaskId' => [
			'NAME' => 'ID',
			'TYPE' => 'int'
		],
		'Comments' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_CM_1'),
			'TYPE' => 'string',
		],
		'ReviewedCount' => [
			'NAME' => Loc::getMessage('BPAR_DESCR_RC'),
			'TYPE' => 'int',
		],
		'TotalCount' => [
			'NAME' => Loc::getMessage('BPAR_DESCR_TC'),
			'TYPE' => 'int',
		],
		'IsTimeout' => [
			'NAME' => Loc::getMessage('BPAR_DESCR_TA1'),
			'TYPE' => 'int',
		],
		'LastReviewer' => [
			'NAME' => Loc::getMessage('BPAR_DESCR_LR'),
			'TYPE' => 'user',
		],
		'LastReviewerComment' => [
			'NAME' => Loc::getMessage('BPAR_DESCR_LR_COMMENT_1'),
			'TYPE' => 'string',
		],
	],
];