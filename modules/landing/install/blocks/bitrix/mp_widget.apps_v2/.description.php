<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_MPWIDGET_APPS_V2_NAME'),
		'type' => ['mainpage'],
		'section' => ['widgets_new_employees', 'widgets_hr'],
		'disableEditButton' => Option::get('landing', 'use_demo_data_in_block_widgets') === 'Y',
	],
	'nodes' => [
		"bitrix:landing.blocks.mp_widget.apps" => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'TITLE_MOBILE' => [],
					'TITLE_DESKTOP' => [],
					// visual
					'COLOR_TITLE_MOBILE' => [
						'style' => true,
					],
					'COLOR_TITLE_DESKTOP' => [
						'style' => true,
					],
					'COLOR_TEXT_MOBILE' => [
						'style' => true,
					],
					'COLOR_TEXT_DESKTOP' => [
						'style' => true,
					],
					'COLOR_BUTTON_MOBILE_V2' => [
						'style' => true,
					],
					'COLOR_BUTTON_TEXT_MOBILE' => [
						'style' => true,
					],
					'COLOR_BUTTON_DESKTOP_V2' => [
						'style' => true,
					],
					'COLOR_BUTTON_TEXT_DESKTOP' => [
						'style' => true,
					],
				],
			],
		],
	],
	'style' => [
		'block' => [
			'type' => ['margin-bottom', 'widget-type'],
		],
	],
];

return $return;