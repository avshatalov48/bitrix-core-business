<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
	'PARAMETERS' => [
		'TITLE_MOBILE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_APPS_PARAM_TITLE_NAME'),
			'TYPE' => 'STRING',
			'DEFAULT' => Loc::getMessage('LANDING_WIDGET_APPS_MOBILE_TITLE_DEFAULT_VALUE'),
		],
		'TITLE_DESKTOP' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_APPS_PARAM_TITLE_NAME'),
			'TYPE' => 'STRING',
			'DEFAULT' => Loc::getMessage('LANDING_WIDGET_APPS_DESKTOP_TITLE_DEFAULT_VALUE'),
		],
		'COLOR_TITLE_MOBILE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_APPS_PARAM_COLOR_TITLE_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_TITLE_DESKTOP' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_APPS_PARAM_COLOR_TITLE_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_TEXT_MOBILE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_APPS_PARAM_COLOR_TEXT_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_TEXT_DESKTOP' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_APPS_PARAM_COLOR_TEXT_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BUTTON_MOBILE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_APPS_PARAM_COLOR_BUTTON_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BUTTON_MOBILE_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_APPS_PARAM_COLOR_BUTTON_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BUTTON_DESKTOP' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_APPS_PARAM_COLOR_BUTTON_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BUTTON_DESKTOP_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_APPS_PARAM_COLOR_BUTTON_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BUTTON_TEXT_MOBILE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_APPS_PARAM_COLOR_BUTTON_TEXT_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BUTTON_TEXT_DESKTOP' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_APPS_PARAM_COLOR_BUTTON_TEXT_NAME'),
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

$arComponentParameters['PARAMETERS']['COLOR_TITLE_MOBILE']['DEFAULT'] = '#333333';
$arComponentParameters['PARAMETERS']['COLOR_TITLE_DESKTOP']['DEFAULT'] = '#333333';
$arComponentParameters['PARAMETERS']['COLOR_TEXT_MOBILE']['DEFAULT'] = '#6a737f';
$arComponentParameters['PARAMETERS']['COLOR_TEXT_DESKTOP']['DEFAULT'] = '#6a737f';
$arComponentParameters['PARAMETERS']['COLOR_BUTTON_MOBILE']['DEFAULT'] = '#2fc6f6';
$arComponentParameters['PARAMETERS']['COLOR_BUTTON_MOBILE_V2']['DEFAULT'] = 'var(--primary)';
$arComponentParameters['PARAMETERS']['COLOR_BUTTON_TEXT_MOBILE']['DEFAULT'] = '#ffffff';
$arComponentParameters['PARAMETERS']['COLOR_BUTTON_DESKTOP']['DEFAULT'] = '#2fc6f6';
$arComponentParameters['PARAMETERS']['COLOR_BUTTON_DESKTOP_V2']['DEFAULT'] = 'var(--primary)';
$arComponentParameters['PARAMETERS']['COLOR_BUTTON_TEXT_DESKTOP']['DEFAULT'] = '#ffffff';
