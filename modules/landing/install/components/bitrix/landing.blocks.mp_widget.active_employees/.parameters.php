<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentParameters = [
	'PARAMETERS' => [
		'TITLE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_TITLE_DEFAULT_VALUE'),
		],
		'PERIOD' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_PERIOD'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'VALUES' => [
				'day' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_PERIOD_DAY'),
				'week' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_PERIOD_WEEK'),
				'month' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_PERIOD_MONTH'),
				'year' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_PERIOD_YEAR'),
			],
			'DEFAULT' => 'month',
		],
		'COLOR_SUBTITLE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_COLOR_SUBTITLE'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_SUBTITLE_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_COLOR_SUBTITLE'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_DIAGRAM_MAIN' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_COLOR_DIAGRAM_MAIN'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_DIAGRAMS' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_COLOR_DIAGRAMS'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_DIAGRAM_TITLE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_COLOR_DIAGRAM_TITLE'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_DIAGRAM_TITLE_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_COLOR_DIAGRAM_TITLE'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_DIAGRAM_TEXT' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_COLOR_DIAGRAM_TEXT'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BORDER_LINE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_COLOR_BORDER_LINE'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_HEADERS_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_PARAMS_COLOR_HEADER'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
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

$arComponentParameters['PARAMETERS']['COLOR_SUBTITLE']['DEFAULT'] = '#000000';
$arComponentParameters['PARAMETERS']['COLOR_SUBTITLE_V2']['DEFAULT'] = '#ffffff';
$arComponentParameters['PARAMETERS']['COLOR_TEXT']['DEFAULT'] = '#adadad';
$arComponentParameters['PARAMETERS']['COLOR_DIAGRAM_MAIN']['DEFAULT'] = '#55d0e0';
$arComponentParameters['PARAMETERS']['COLOR_BORDER_LINE']['DEFAULT'] = 'hsla(212, 8%, 61%, 0.15)';
$arComponentParameters['PARAMETERS']['COLOR_DIAGRAM_TITLE']['DEFAULT'] = '#333333';
$arComponentParameters['PARAMETERS']['COLOR_DIAGRAM_TITLE_V2']['DEFAULT'] = '#ffffff';
$arComponentParameters['PARAMETERS']['COLOR_DIAGRAM_TEXT']['DEFAULT'] = '#58616e';
$arComponentParameters['PARAMETERS']['COLOR_HEADERS_V2']['DEFAULT'] = '#ffffff';
