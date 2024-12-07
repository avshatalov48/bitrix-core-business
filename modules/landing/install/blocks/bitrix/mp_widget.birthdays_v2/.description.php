<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_BIRTHDAYS_V2_NAME'),
		'type' => ['mainpage'],
		'section' => ['widgets_team', 'widgets_hr'],
		'disableEditButton' => Option::get('landing', 'use_demo_data_in_block_widgets') === 'Y',
	],
	'nodes' => [
		"bitrix:landing.blocks.mp_widget.birthdays" => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'TITLE' => [],
					// visual
					'COLOR_HEADERS' => [
						'style' => true,
					],
					'COLOR_BG_V2' => [
						'style' => true,
					],
					'COLOR_USER_BORDER' => [
						'style' => true,
					],
					'COLOR_NAME_V2' => [
						'style' => true,
					],
					'COLOR_WORK_POSITION_V2' => [
						'style' => true,
					],
					'COLOR_DATE_V2' => [
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