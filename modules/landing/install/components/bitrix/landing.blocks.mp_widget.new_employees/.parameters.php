<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
	'PARAMETERS' => [
		'TITLE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_PARAM_TITLE_NAME'),
			'TYPE' => 'STRING',
			'DEFAULT' => Loc::getMessage('LANDING_MPWIDGET_NEW_EMPLOYEES_TITLE_DEFAULT_VALUE'),
		],
		'COLOR_HEADERS_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_HEADERS'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
	],
];

$parentComponentParameters = @\CComponentUtil::GetComponentProps(
	'bitrix:landing.blocks.mp_widget.base',
);
$arComponentParameters['PARAMETERS'] = array_merge(
	$parentComponentParameters['PARAMETERS'],
	$arComponentParameters['PARAMETERS']
);

$arComponentParameters['PARAMETERS']['COLOR_HEADERS']['DEFAULT'] = '#333333';
$arComponentParameters['PARAMETERS']['COLOR_HEADERS_V2']['DEFAULT'] = 'var(--primary)';