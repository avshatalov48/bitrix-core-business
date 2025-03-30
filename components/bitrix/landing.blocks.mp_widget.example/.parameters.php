<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
	'PARAMETERS' => [
		'TITLE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_TITLE_DEFAULT_VALUE'),
		],
		'USER_AMOUNT' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_USER_AMOUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => 3,
		],
	],
];

$parentComponentParameters = CComponentUtil::GetComponentProps(
	'bitrix:landing.blocks.mp_widget.base',
);
$arComponentParameters['PARAMETERS'] = array_merge(
	$parentComponentParameters['PARAMETERS'],
	$arComponentParameters['PARAMETERS']
);
