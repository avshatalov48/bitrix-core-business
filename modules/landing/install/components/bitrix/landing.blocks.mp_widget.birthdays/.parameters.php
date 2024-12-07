<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
	'PARAMETERS' => [
		'TITLE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_TITLE_DEFAULT_VALUE'),
		],
		'COLOR_BG' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_BG'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BG_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_BG'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_USER_BORDER' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_USER_BORDER'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_NAME' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_NAME_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_NAME'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_WORK_POSITION' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_WORK_POSITION'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_WORK_POSITION_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_WORK_POSITION'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_DATE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_DATE'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_DATE_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_DATE'),
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

//todo: not work opacity in color control
$arComponentParameters['PARAMETERS']['COLOR_BG']['DEFAULT'] = 'hsla(40, 100%, 50%, 0.15)';
//todo: not work primary-opacity in color control
$arComponentParameters['PARAMETERS']['COLOR_BG_V2']['DEFAULT'] = 'var(--primary-opacity-0_5)';
$arComponentParameters['PARAMETERS']['COLOR_HEADERS']['DEFAULT'] = '#E89B06';
$arComponentParameters['PARAMETERS']['COLOR_USER_BORDER']['DEFAULT'] = '#ffa900';
$arComponentParameters['PARAMETERS']['COLOR_NAME']['DEFAULT'] = '#525c69';
$arComponentParameters['PARAMETERS']['COLOR_NAME_V2']['DEFAULT'] = '#ffffff';
$arComponentParameters['PARAMETERS']['COLOR_WORK_POSITION']['DEFAULT'] = '#959ca4';
//todo: not work opacity in color control
$arComponentParameters['PARAMETERS']['COLOR_WORK_POSITION_V2']['DEFAULT'] = 'hsla(0, 0%, 100%, 0.65)';
$arComponentParameters['PARAMETERS']['COLOR_DATE']['DEFAULT'] = 'var(--primary)';
$arComponentParameters['PARAMETERS']['COLOR_BG_BORDER']['DEFAULT'] = 'var(--primary)';
$arComponentParameters['PARAMETERS']['COLOR_DATE_V2']['DEFAULT'] = '#f7a70b';