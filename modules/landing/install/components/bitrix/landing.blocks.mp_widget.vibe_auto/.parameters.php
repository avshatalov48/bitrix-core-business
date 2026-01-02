<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentParameters = [
	'PARAMETERS' => [
		'TYPE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_VIBE_AUTO_TITLE'),
			'TYPE' => 'LIST',
			'VALUES' => [
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
			],
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
