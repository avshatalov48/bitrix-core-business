<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('BPGUIA_DESCR_NAME_1'),
	'DESCRIPTION' => Loc::getMessage('BPGUIA_DESCR_DESCR_MSGVER_1'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'GetUserInfoActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'other',
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee',
		'GROUP' => ['other'],
		'SORT' => 3400,
		'IS_SUPPORTING_ROBOT' => true,
	],
	'RETURN' => [],
];

$userService = CBPRuntime::getRuntime()->getUserService();
foreach ($userService->getUserBaseFields() as $key => $property)
{
	$arActivityDescription['RETURN']['USER_' . $key] = array_change_key_case($property, CASE_UPPER);
}
// compatibility
$arActivityDescription['RETURN']['IS_ABSENT'] = [
	'NAME' => Loc::getMessage('BPGUIA_DESCR_IS_ABSENT'),
	'TYPE' => 'bool',
];
$arActivityDescription['RETURN']['TIMEMAN_STATUS'] = [
	'NAME' => Loc::getMessage('BPGUIA_DESCR_TIMEMAN_STATUS'),
	'TYPE' => 'string',
];

foreach ($userService->getUserExtendedFields() as $key => $property)
{
	if ($key === 'UF_DEPARTMENT')
	{
		unset($arActivityDescription['RETURN']['USER_UF_DEPARTMENT']);
		$key = 'USER_UF_DEPARTMENT';
	}

	$arActivityDescription['RETURN'][$key] = array_change_key_case($property, CASE_UPPER);
}
