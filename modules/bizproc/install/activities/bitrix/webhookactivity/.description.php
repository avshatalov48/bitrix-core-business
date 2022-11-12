<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Rest;
use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPWHA_DESCR_NAME_1'),
	'DESCRIPTION' => Loc::getMessage('BPWHA_DESCR_DESCR_1'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'WebHookActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'other',
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'other',
		'GROUP' => ['other'],
		'ASSOCIATED_TRIGGERS' => [
			'WEBHOOK' => 1,
		],
		'SORT' => 4000,
	],
];

if (
	!Main\Loader::includeModule('rest')
	|| !Rest\Engine\Access::isAvailable()
)
{
	$arActivityDescription['EXCLUDED'] = true;
}
