<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_31_8_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['tiles', 'widgets_text'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_8_NODES_CARD'),
			'label' => ['.landing-block-node-card-icon', '.landing-block-node-card-text'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_8_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_8_ICON'),
			'type' => 'icon',
		],
		'.landing-block-node-card-text' => [
			Loc::getMessage('LANDING_BLOCK_31_8_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_8_NODES_BUTTON'),
			'type' => 'link',
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_8_NODES_IMG'),
			'type' => 'img',
			'dimensions' => ['height' => 1080],
			'create2xByDefault' => false,
		],
	],
	'style' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_8_TITLE'),
			'type' => ['typo', 'animation', 'heading'],
		],
		'.landing-block-node-content-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_8_ICON'),
			'type' => ['padding-top', 'padding-bottom'],
		],
		'.landing-block-node-card-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_8_ICON'),
			'type' => ['color'],
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_8_TEXT'),
			'type' => ['typo'],
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_8_NODES_BUTTON'),
			'type' => ['button', 'animation'],
		],
		'.landing-block-node-button-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_1_BUTTON_AREA'),
			'type' => ['text-align'],
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_8_NODES_IMG'),
			'type' => ['background-size'],
		],
	],
];