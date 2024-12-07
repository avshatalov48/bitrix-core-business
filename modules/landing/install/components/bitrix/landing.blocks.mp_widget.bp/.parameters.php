<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
	'PARAMETERS' => [
		'TITLE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BP_PARAM_TITLE_NAME'),
			'TYPE' => 'STRING',
			'DEFAULT' => Loc::getMessage('LANDING_WIDGET_BP_TITLE_DEFAULT_VALUE'),
		],
		'BUTTON' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BP_PARAM_BUTTON_NAME'),
			'TYPE' => 'STRING',
			'DEFAULT' => Loc::getMessage('LANDING_WIDGET_BP_BUTTON_DEFAULT_VALUE'),
		],
		'SORT' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BP_PARAM_SORT_NAME'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'VALUES' => [
				'popularHighToLow' => Loc::getMessage('LANDING_WIDGET_BP_PARAM_SORT_POPULAR_TO_LOW'),
				'popularLowToHigh' => Loc::getMessage('LANDING_WIDGET_BP_PARAM_SORT_POPULAR_TO_HIGH'),
			],
			'DEFAULT' => 'popularHighToLow',
		],
		'COLOR_BG' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BP_PARAM_BG_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BG_BUTTON' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BP_PARAM_BG_BUTTON_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BG_BUTTON_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BP_PARAM_BG_BUTTON_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BUTTON_TEXT' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BP_PARAM_COLOR_BUTTON_TEXT_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BUTTON_TEXT_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BP_PARAM_COLOR_BUTTON_TEXT_NAME'),
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

$arComponentParameters['PARAMETERS']['COLOR_HEADERS']['DEFAULT'] = 'var(--primary)';
$arComponentParameters['PARAMETERS']['COLOR_BG']['DEFAULT'] = '#f3fbfe';
$arComponentParameters['PARAMETERS']['COLOR_BUTTON']['DEFAULT'] = '#bdc1c6';