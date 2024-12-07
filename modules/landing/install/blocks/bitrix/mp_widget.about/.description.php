<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_ABOUT_NAME'),
		'type' => ['mainpage'],
		'section' => ['widgets_company_life', 'widgets_hr'],
		'disableEditButton' => Option::get('landing', 'use_demo_data_in_block_widgets') === 'Y',
	],
	'nodes' => [
		"bitrix:landing.blocks.mp_widget.about" => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'TITLE' => [],
					'TEXT' => [],
 					'BOSS_ID' => [],
					'SHOW_EMPLOYEES' => [],
					'SHOW_SUPERVISORS' => [],
					'SHOW_DEPARTMENTS' => [],
					'COLOR_HEADERS' => [
						'style' => true,
					],
					'COLOR_TEXT' => [
						'style' => true,
					],
					'COLOR_ICON' => [
						'style' => true,
					],
					'COLOR_BORDER' => [
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