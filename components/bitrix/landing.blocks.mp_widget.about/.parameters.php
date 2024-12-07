<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$bossIdDefault = 1;
if (Loader::includeModule('intranet'))
{
	$structure = CIntranetUtils::getStructure();
	foreach ($structure['DATA'] as $dataItem)
	{
		if ($dataItem['UF_HEAD'] !== null)
		{
			$bossIdDefault = (int)$dataItem['UF_HEAD'];
			break;
		}
	}
}

$arComponentParameters = [
	'PARAMETERS' => [
		'TITLE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ABOUT_PARAMS_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => Loc::getMessage('LANDING_WIDGET_ABOUT_PARAMS_TITLE_DEFAULT_VALUE'),
		],
		'TEXT' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ABOUT_PARAMS_TEXT'),
			'TYPE' => 'STRING',
			'DEFAULT' => Loc::getMessage('LANDING_WIDGET_ABOUT_PARAMS_TEXT_DEFAULT_VALUE'),
		],
		'BOSS_ID' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ABOUT_PARAMS_BOSS_ID'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initUserSelectField',
			'DEFAULT' => $bossIdDefault,
		],
		'SHOW_EMPLOYEES' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ABOUT_PARAMS_SHOW_EMPLOYEES'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'SHOW_SUPERVISORS' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ABOUT_PARAMS_SHOW_SUPERVISORS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'SHOW_DEPARTMENTS' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ABOUT_PARAMS_SHOW_DEPARTMENTS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'COLOR_ICON' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ABOUT_PARAMS_COLOR_ICON'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BORDER' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ABOUT_PARAMS_COLOR_BORDER'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_TEXT_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ABOUT_PARAMS_COLOR_TEXT'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
		'COLOR_BORDER_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_ABOUT_PARAMS_COLOR_BORDER'),
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

$arComponentParameters['PARAMETERS']['COLOR_HEADERS']['DEFAULT'] = '#ffffff';
$arComponentParameters['PARAMETERS']['COLOR_TEXT']['DEFAULT'] = '#ffffff';
$arComponentParameters['PARAMETERS']['COLOR_TEXT_V2']['DEFAULT'] = 'hsla(0, 0%, 100%, 0.6)';
$arComponentParameters['PARAMETERS']['COLOR_ICON']['DEFAULT'] = 'var(--primary)';
$arComponentParameters['PARAMETERS']['COLOR_BORDER']['DEFAULT'] = 'hsl(210, 3%, 76%, 0.7)';
$arComponentParameters['PARAMETERS']['COLOR_BORDER_V2']['DEFAULT'] = 'var(--primary)';
