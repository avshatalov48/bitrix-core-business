<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Mainpage;
use \Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_BIRTHDAYS_NAME'),
		'type' => ['mainpage'],
		'section' => ['widgets_team', 'widgets_hr'],
		'disableEditButton' => Mainpage\Manager::isUseDemoData(),
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
					'COLOR_BG' => [
						'style' => true,
					],
					'COLOR_USER_BORDER' => [
						'style' => true,
					],
					'COLOR_NAME' => [
						'style' => true,
					],
					'COLOR_WORK_POSITION' => [
						'style' => true,
					],
					'COLOR_DATE' => [
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