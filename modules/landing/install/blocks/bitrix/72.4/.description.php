<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['widgets_columns'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_CARD'),
			'label' => ['.landing-block-node-card-text'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-image' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_IMAGE'),
			'type' => 'img',
		],
		'.landing-block-node-card-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_CARD_ICON'),
			'type' => 'icon',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_CARD_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget', 'padding-left', 'padding-right'],
		],
		'nodes' => [
			'.landing-block-node-content' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_CONTAINER'),
				'type' => ['container-max-width'],
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-image' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_IMAGE'),
				'type' => ['border-radius'],
			],
			'.landing-block-node-containers' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_CONTAINERS'),
				'type' => ['align-items'],
			],
			'.landing-block-node-image-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_CONTAINER'),
				'type' => ['margin-top', 'margin-bottom'],
			],
			'.landing-block-node-cards-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_CONTAINER'),
				'type' => ['background-color', 'margin-top', 'margin-bottom', 'border-radius'],
			],
			'.landing-block-node-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_CONTAINER_CARD'),
				'type' => ['align-items'],
			],
			'.landing-block-node-card-icon' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_CARD_ICON'),
				'type' => ['background-color', 'color'],
			],
			'.landing-block-node-card-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_4_NODE_CARD_TEXT'),
				'type' => ['typo'],
			],
		],
	],
];

return $return;