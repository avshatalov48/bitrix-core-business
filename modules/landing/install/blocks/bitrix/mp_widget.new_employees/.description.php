<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Mainpage;
use \Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_NEW_EMPLOYEES_NAME'),
		'type' => ['mainpage'],
		'section' => ['widgets_team', 'widgets_hr'],
		'disableEditButton' => Mainpage\Manager::isUseDemoData(),
	],
	'nodes' => [
		"bitrix:landing.blocks.mp_widget.new_employees" => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'TITLE' => [],
					// visual
					'COLOR_HEADERS' => [
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