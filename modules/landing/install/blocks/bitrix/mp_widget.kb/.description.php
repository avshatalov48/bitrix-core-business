<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_KB_NAME'),
		'type' => ['mainpage'],
		'section' => ['widgets_new_employees', 'widgets_hr'],
		'attrsFormDescription' => Loc::getMessage('LANDING_BLOCK_WIDGET_KB_HINT'),
		'attrsFormDescriptionHintStyle' => 'blueHint',
		'disableEditButton' => Option::get('landing', 'use_demo_data_in_block_widgets') === 'Y',
	],
	'nodes' => [
		"bitrix:landing.blocks.mp_widget.kb" => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'TITLE' => [],
					'SORT' => [],
					// visual
					'COLOR_HEADERS' => [
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