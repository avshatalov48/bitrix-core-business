<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Mainpage;
use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_ACTIVE_EMPLOYEES_NAME'),
		'type' => ['mainpage'],
		'section' => ['widgets_team', 'widgets_hr'],
		'disableEditButton' => Mainpage\Manager::isUseDemoData(),
	],
	'nodes' => [
		"bitrix:landing.blocks.mp_widget.active_employees" => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'TITLE' => [],
					'PERIOD' => [],
					// visual
					'COLOR_HEADERS' => [
						'style' => true,
					],
					'COLOR_SUBTITLE' => [
						'style' => true,
					],
					'COLOR_TEXT' => [
						'style' => true,
					],
					'COLOR_DIAGRAM_MAIN' => [
						'style' => true,
					],
					'COLOR_DIAGRAMS' => [
						'style' => true,
					],
					'COLOR_DIAGRAM_TITLE' => [
						'style' => true,
					],
					'COLOR_DIAGRAM_TEXT' => [
						'style' => true,
					],
					'COLOR_BORDER_LINE' => [
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