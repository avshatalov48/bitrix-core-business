<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['text_image', 'widgets_image'],
	],
	'cards' => [
		'.landing-block-node-list-item' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_NODE_CARD'),
			'label' => ['.landing-block-node-list-text'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_NODE_SUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-list-item-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_STYLE_ARROW'),
			'type' => 'icon',
		],
		'.landing-block-node-list-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_NODE_CARD_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_NODE_BUTTON_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_NODE_LINK'),
			'type' => 'link',
			'skipContent' => true,
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_NODE_IMG'),
			'type' => 'img',
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-subtitle' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_NODE_SUBTITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_STYLE_CONTAINER'),
				'type' => ['background', 'padding-top', 'padding-bottom'],
			],
			'.landing-block-node-list-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_NODE_CARD_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-list-item-icon' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_STYLE_ARROW'),
				'type' => ['color', 'font-size'],
			],
			'.landing-block-node-button' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_8_STYLE_BUTTON'),
				'type' => ['background', 'color', 'background-hover', 'color-hover', 'font-family'],
			],
		],
	],
];

return $return;