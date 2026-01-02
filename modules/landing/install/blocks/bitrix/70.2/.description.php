<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['tiles', 'widgets_tiles'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_CARD'),
			'label' => ['.landing-block-node-card-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_LINK'),
			'type' => 'link',
			'skipContent' => true,
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_CARD_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_ICON'),
			'type' => 'icon',
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-node-content' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_CONTAINER'),
				'type' => ['container-max-width'],
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-card-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_CONTAINER'),
				'type' => ['container-max-width', 'background-color', 'border-radius'],
			],
			'.landing-block-node-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_CARD'),
				'type' => [],
			],
			'.landing-block-node-card-link' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_CARD'),
				'type' => ['background-color', 'background-color-hover', 'border-radius'],
			],
			'.landing-block-node-card-icon' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_ICON'),
				'type' => ['background-color', 'color'],
			],
			'.landing-block-node-card-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_2_NODE_CARD_TITLE'),
				'type' => ['typo', 'color-hover'],
			],
		],
	],
];

return $return;