<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPSWFA_DESCR_NAME_1'),
	'DESCRIPTION' => Loc::getMessage('BPSWFA_DESCR_DESCR_1'),
	'TYPE' => ['activity'],
	'CLASS' => 'StartWorkflowActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'document',
	],
	'RETURN' => [
		'WorkflowId' => [
			'NAME' => Loc::getMessage('BPSWFA_DESCR_WORKFLOW_ID'),
			'TYPE' => 'string',
		],
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee',
		'GROUP' => ['other'],
		'SORT' => 3600,
	],
];

if (
	isset($documentType)
	&& $documentType[0] === 'crm'
	&& CModule::IncludeModule('crm')
	&& \Bitrix\Crm\Automation\Factory::canUseBizprocDesigner()
)
{
	$arActivityDescription['TYPE'][] = 'robot_activity';
}
