<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['text_image', 'widgets_image'],
	],
	'cards' => [
		'.landing-block-node-list-item' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_CARD'),
			'label' => ['.landing-block-node-list-item-text'],
		],
		'.landing-block-node-button-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_CARD'),
			'label' => ['.landing-block-node-right-button-text'],
		],
	],
	'nodes' => [
		//left
		'.landing-block-node-title-left' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text-left' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-list-item-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_ARROW'),
			'type' => 'icon',
		],
		'.landing-block-node-list-item-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button-left-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_CARD_BUTTON_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button-left' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_CARD_LINK'),
			'type' => 'link',
			'skipContent' => true,
		],
		'.landing-block-node-left' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_IMG'),
			'type' => 'img',
		],
		//right
		'.landing-block-node-title-right' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text-right' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-right-button-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_CARD_BUTTON_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_CARD_LINK'),
			'type' => 'link',
			'skipContent' => true,
		],
		'.landing-block-node-right' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_IMG'),
			'type' => 'img',
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			//left
			'.landing-block-node-title-left' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-text-left' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-list-item-icon' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_ARROW'),
				'type' => ['color', 'font-size'],
			],
			'.landing-block-node-list-item-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_ITEM'),
				'type' => ['typo'],
			],
			'.landing-block-node-button-left-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_BUTTON'),
				'type' => ['color', 'color-hover', 'background-color', 'background-color-hover', 'font-family'],
			],
			//right
			'.landing-block-node-title-right' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-text-right' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-right-button-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_7_NODE_BUTTON'),
				'type' => ['color', 'color-hover', 'background-color', 'background-color-hover', 'font-family'],
			],
		],
	],
];

return $return;