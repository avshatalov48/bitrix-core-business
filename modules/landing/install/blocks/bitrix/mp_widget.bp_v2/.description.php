<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_BP_V2_NAME'),
		'type' => ['mainpage'],
		'section' => ['widgets_automation', 'widgets_hr'],
		'disableEditButton' => Option::get('landing', 'use_demo_data_in_block_widgets') === 'Y',
	],
	'nodes' => [
		"bitrix:landing.blocks.mp_widget.bp" => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'TITLE' => [],
					'SORT' => [],
					'BUTTON' => [],
					// visual
					'COLOR_HEADERS' => [
						'style' => true,
					],
					'COLOR_BG_BUTTON_V2' => [
						'style' => true,
					],
					'COLOR_BUTTON_TEXT_V2' => [
						'style' => true,
					],
					'COLOR_BUTTON' => [
						'style' => true,
					],
				],
			],
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
	],
];

return $return;