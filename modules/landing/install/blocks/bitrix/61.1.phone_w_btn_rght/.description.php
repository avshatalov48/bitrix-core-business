<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LNDNG_BLCK_61_1_NAME'),
		'section' => ['contacts'],
		'type' => 'null',
	],
	'cards' => [],
	'nodes' => [
		'bitrix:landing.blocks.crm_contacts' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					// visual
					'TEMPLATE_MODE' => [
						'style' => true,
					],
					'BUTTON_POSITION' => [
						'style' => true,
					],
					'TITLE' => [],
					'BUTTON_TITLE' => [],
					'BUTTON_CLASSES' => [
						'hidden' => true,
					],
				],
			],
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'block-border', 'animation'],
		],
		'nodes' => [

		],
	],
];