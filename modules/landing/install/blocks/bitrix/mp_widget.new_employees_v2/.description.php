<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_NEW_EMPLOYEES_V2_NAME'),
		'type' => ['mainpage'],
		'section' => ['widgets_team', 'widgets_hr'],
		'disableEditButton' => Option::get('landing', 'use_demo_data_in_block_widgets') === 'Y',
	],
	'nodes' => [
		"bitrix:landing.blocks.mp_widget.new_employees" => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'TITLE' => [],
					// visual
					'COLOR_HEADERS_V2' => [
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