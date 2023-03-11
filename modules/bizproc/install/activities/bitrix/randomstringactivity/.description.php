<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPRNDSA_DESCR_NAME'),
	'DESCRIPTION' => Loc::getMessage('BPRNDSA_DESCR_DESCR_MSGVER_1'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'RandomStringActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'other',
	],
	'RETURN' => [
		'ResultString' => [
			'NAME' => Loc::getMessage('BPRNDSA_DESCR_RESULT_STRING'),
			'TYPE' => 'string',
		],
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee',
		'GROUP' => ['other'],
		'SORT' => 3900,
		'IS_SUPPORTING_ROBOT' => true,
	],
];