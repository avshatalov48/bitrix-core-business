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
				],
			],
		],
		// '.landing-block-node-text' => array(
		// 	'name' => Loc::getMessage('LNDNG_BLCK_61_1_TEXT'),
		// 	'type' => 'text',
		// ),
		// '.landing-block-node-title' => array(
		// 	'name' => Loc::getMessage('LNDNG_BLCK_61_1_TITLE'),
		// 	'type' => 'text',
		// ),
		// '.landing-block-node-button' => array(
		// 	'name' => Loc::getMessage('LNDNG_BLCK_61_1_BTN'),
		// 	'type' => 'link',
		// ),
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'block-border', 'animation'],
		],
		'nodes' => [
			// '.landing-block-node-container' => array(
			// 	'name' => Loc::getMessage('LNDNG_BLCK_61_1_CONTAINER'),
			// 	'type' => 'align-items',
			// ),
			// '.landing-block-node-text-container' => array(
			// 	'name' => Loc::getMessage('LNDNG_BLCK_61_1_TEXT'),
			// 	'type' => 'animation',
			// ),
			// '.landing-block-node-title' => array(
			// 	'name' => Loc::getMessage('LNDNG_BLCK_61_1_TITLE'),
			// 	'type' => 'typo',
			// ),
			// '.landing-block-node-text' => array(
			// 	'name' => Loc::getMessage('LNDNG_BLCK_61_1_TEXT'),
			// 	'type' => 'typo',
			// ),
			// '.landing-block-node-button' => array(
			// 	'name' => Loc::getMessage('LNDNG_BLCK_61_1_BTN'),
			// 	'type' => array('button'),
			// ),
			// '.landing-block-node-button-container' => array(
			// 	'name' => Loc::getMessage('LNDNG_BLCK_61_1_BTN'),
			// 	'type' => array('text-align', 'animation'),
			// ),
		],
	],
];