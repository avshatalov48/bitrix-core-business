<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPAA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPAA_DESCR_DESCR'),
	'TYPE' => 'activity',
	'CLASS' => 'ApproveActivity',
	'JSCLASS' => 'ApproveActivity',
	'CATEGORY' => [
		'ID' => 'document',
		'OWN_ID' => 'task',
		'OWN_NAME' => Loc::getMessage('BPAA_DESCR_TASKS'),
	],
	'RETURN' => [
		'TaskId' => [
			'NAME' => 'ID',
			'TYPE' => 'int',
		],
		'Comments' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_CM'),
			'TYPE' => 'string',
		],
		'VotedCount' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_VC'),
			'TYPE' => 'int',
		],
		'TotalCount' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_TC'),
			'TYPE' => 'int',
		],
		'VotedPercent' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_VP'),
			'TYPE' => 'int',
		],
		'ApprovedPercent' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_AP'),
			'TYPE' => 'int',
		],
		'NotApprovedPercent' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_NAP'),
			'TYPE' => 'int',
		],
		'ApprovedCount' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_AC'),
			'TYPE' => 'int',
		],
		'NotApprovedCount' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_NAC'),
			'TYPE' => 'int',
		],
		'LastApprover' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_LA'),
			'TYPE' => 'user',
		],
		'LastApproverComment' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_LA_COMMENT'),
			'TYPE' => 'string',
		],
		'UserApprovers' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_APPROVERS'),
			'TYPE' => 'user',
		],
		'Approvers' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_APPROVERS_STRING'),
			'TYPE' => 'string',
		],
		'UserRejecters' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_REJECTERS'),
			'TYPE' => 'user',
		],
		'Rejecters' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_REJECTERS_STRING'),
			'TYPE' => 'string',
		],
		'IsTimeout' => [
			'NAME' => Loc::getMessage('BPAA_DESCR_TA1'),
			'TYPE' => 'int',
		],
	],
	'SORT' => 100,
];
