<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['widgets_columns'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_CARD'),
			'label' => ['.landing-block-node-card-text'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_CARD_ICON'),
			'type' => 'icon',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_CARD_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_BUTTON'),
			'type' => 'link',
		],
		'.landing-block-node-image' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_IMAGE'),
			'type' => 'img',
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-node-containers' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_CONTAINERS'),
				'type' => ['align-items', 'container-max-width'],
			],
			'.landing-block-node-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_CONTAINER'),
				'type' => ['padding-left', 'padding-right'],
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-cards-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_CONTAINER'),
				'type' => ['margin-top', 'margin-bottom'],
			],
			'.landing-block-node-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_CONTAINER_CARD'),
				'type' => ['align-items'],
			],
			'.landing-block-node-card-icon' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_CARD_ICON'),
				'type' => ['background-color', 'color'],
			],
			'.landing-block-node-card-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_CARD_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-button' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_BUTTON'),
				'type' => ['background', 'color', 'background-hover', 'color-hover', 'font-family'],
			],
			'.landing-block-node-image-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_CONTAINER'),
				'type' => ['row-align'],
			],
			'.landing-block-node-image' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_3_NODE_IMAGE'),
				'type' => ['border-radius'],
			],
		],
	],
];

return $return;