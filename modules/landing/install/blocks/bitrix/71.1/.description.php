<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['columns', 'widgets_columns'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_CARD'),
			'label' => ['.landing-block-node-card-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-info-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_IMG'),
			'type' => 'img',
		],
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_CARD'),
			'type' => 'link',
			'skipContent' => true,
		],
		'.landing-block-node-card-tab' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_BADGE'),
			'type' => 'text',
			'allowInlineEdit' => false,
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_CARD_TITLE'),
			'type' => 'text',
			'allowInlineEdit' => false,
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_CARD_TEXT'),
			'type' => 'text',
			'allowInlineEdit' => false,
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-info-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-img-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_IMG_CONTAINER'),
				'type' => ['background-color'],
			],
			'.landing-block-node-card-tab' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_BADGE'),
				'type' => ['background-color', 'color'],
			],
			'.landing-block-node-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_CARD'),
				'type' => ['border-color', 'background-color', 'border-color-hover', 'background-hover', 'padding-top', 'padding-bottom'],
			],
			'.landing-block-node-card-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_CARD_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-card-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_1_NODE_CARD_TEXT'),
				'type' => ['typo'],
			],
		],
	],
];

return $return;