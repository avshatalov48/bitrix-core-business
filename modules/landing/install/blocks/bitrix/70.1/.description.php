<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_1_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['tiles', 'widgets_tiles'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_1_NODE_CARD'),
			'label' => ['.landing-block-node-item-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_1_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-item-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_1_NODE_CARD_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-item-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_1_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-item-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_1_NODE_BUTTON'),
			'type' => 'link',
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_1_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_1_NODE_CARD'),
				'type' => ['background-color'],
			],
			'.landing-block-node-item-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_1_NODE_CARD_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-item-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_1_NODE_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-item-button' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_1_NODE_BUTTON'),
				'type' => ['color', 'color-hover', 'background-color', 'background-color-hover', 'font-family'],
			],
		],
	],
];

return $return;