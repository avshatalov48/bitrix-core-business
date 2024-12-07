<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
	'PARAMETERS' => [
		'TITLE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_KB_PARAM_TITLE_NAME'),
			'TYPE' => 'STRING',
			'DEFAULT' => Loc::getMessage('LANDING_WIDGET_KB_TITLE_DEFAULT_VALUE'),
		],
		'SORT' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_KB_PARAM_SORT_NAME'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'VALUES' => [
				'viewsHighToLow' => Loc::getMessage('LANDING_WIDGET_KB_PARAM_SORT_VIEWS_TO_LOW'),
				'viewsLowToHigh' => Loc::getMessage('LANDING_WIDGET_KB_PARAM_SORT_VIEWS_TO_HIGH'),
				'dateModifyLowToHigh' => Loc::getMessage('LANDING_WIDGET_KB_PARAM_SORT_DATE_MODIFY_TO_LOW'),
				'dateModifyHighToLow' => Loc::getMessage('LANDING_WIDGET_KB_PARAM_SORT_DATE_MODIFY_TO_HIGH'),
			],
		],
		'COLOR_HEADERS_V2' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_KB_PARAM_COLOR_HEADERS_NAME'),
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

$arComponentParameters['PARAMETERS']['COLOR_BUTTON_V2']['DEFAULT'] = '#ffffff';
$arComponentParameters['PARAMETERS']['COLOR_HEADERS_V2']['DEFAULT'] = '#ffffff';