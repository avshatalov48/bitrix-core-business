<?php

use Bitrix\Main\Localization\Loc;

/**
 * @var string $componentPath
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentParameters = [
	'PARAMETERS' => [
		'COLOR_TEXT' => [
			'NAME' => Loc::getMessage('LANDING_MPWIDGET_BASE_COLOR_TEXT'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
			'DEFAULT' => '#333333',
		],
		'COLOR_HEADERS' => [
			'NAME' => Loc::getMessage('LANDING_MPWIDGET_BASE_COLOR_HEADERS'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
			'DEFAULT' => '#333333',
		],
		'COLOR_HEADERS_V2' => [
			'NAME' => Loc::getMessage('LANDING_MPWIDGET_BASE_COLOR_HEADERS'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
			'DEFAULT' => '#333333',
		],
		'COLOR_BUTTON' => [
			'NAME' => Loc::getMessage('LANDING_MPWIDGET_BASE_COLOR_BUTTON'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
			'DEFAULT' => '#000000',
		],
		'COLOR_BUTTON_V2' => [
			'NAME' => Loc::getMessage('LANDING_MPWIDGET_BASE_COLOR_BUTTON'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
			'DEFAULT' => '#000000',
		],
	]
];