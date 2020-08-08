<?php

use Bitrix\Landing\Hook\Page\B24button;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentParameters = [
	'PARAMETERS' => [
		'TITLE' => [
			'NAME' => GetMessage('LNDNG_BLPHB_TEMPLATE_TITLE_NAME'),
			'TYPE' => 'STRING',
		],
		'BUTTON_TITLE' => [
			'NAME' => GetMessage('LNDNG_BLPHB_TEMPLATE_TITLE_BUTTON_NAME'),
			'TYPE' => 'STRING',
		],
		'TEMPLATE_MODE' => [
			'NAME' => GetMessage('LNDNG_BLPHB_TEMPLATE_MODE_NAME'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'VALUES' => [
				'darkmode' => GetMessage('LNDNG_BLPHB_TEMPLATE_MODE_DARK'),
				'lightmode' => GetMessage('LNDNG_BLPHB_TEMPLATE_MODE_LIGHT')
			],
			'DEFAULT' => '',
			'COLS' => 25,
			'PARENT' => 'BASE',
		],
		'BUTTON_POSITION' => [
			'NAME' => GetMessage('LNDNG_BLPHB_TEMPLATE_MODE_NAME'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'VALUES' => [
				'right' => GetMessage('LNDNG_BLPHB_BUTTON_POSITION_RIGHT'),
				'left' => GetMessage('LNDNG_BLPHB_BUTTON_POSITION_LEFT')
			],
			'DEFAULT' => '',
			'COLS' => 25,
			'PARENT' => 'BASE',
		],
	],
];