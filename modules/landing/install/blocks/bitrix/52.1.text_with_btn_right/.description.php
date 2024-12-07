<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['title', 'widgets_text'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-BTN'),
			'type' => 'link',
		],
	],
	'style' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-TEXT'),
			'type' => [
				//typo
				'text-align',
				'color',
				'font-size',
				'font-family',
				'font-weight',
				'text-decoration',
				'text-transform',
				'line-height',
				'letter-spacing',
				'word-break',
				'text-shadow',
				'padding-top',
				'padding-left',
				'padding-right',
				'margin-bottom',
				//other
				'animation',
			],
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-BTN'),
			'type' => [
				//button
				'button-color',
				'button-type',
				'button-size',
				'button-padding',
				'border-radius',
				'color',
				'color-hover',
				'font-family',
				'text-transform',
			],
		],
		'.landing-block-node-button-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_1_BTN_AREA'),
			'type' => [
				'text-align',
				'animation',
			],
		],
		'.landing-block-node-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT_CONTAINER'),
			'type' => [
				//container
				'container-max-width',
				'padding-left',
				'padding-right',
			],
		],
	],
];