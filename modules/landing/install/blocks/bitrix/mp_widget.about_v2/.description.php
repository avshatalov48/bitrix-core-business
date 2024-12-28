<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Mainpage;
use \Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_ABOUT_V2_NAME'),
		'type' => ['mainpage'],
		'section' => ['widgets_company_life', 'widgets_hr'],
		'disableEditButton' => Mainpage\Manager::isUseDemoData(),
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
					'COLOR_TEXT_V2' => [
						'style' => true,
					],
					'COLOR_ICON' => [
						'style' => true,
					],
					'COLOR_BORDER_V2' => [
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