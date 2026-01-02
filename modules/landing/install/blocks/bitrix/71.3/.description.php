<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['columns', 'widgets_columns'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_CARD'),
			'label' => ['.landing-block-node-card-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_SUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-tab' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_BADGE'),
			'type' => 'text',
		],
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_CARD'),
			'type' => 'link',
			'skipContent' => true,
		],
		'.landing-block-node-card-badge' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_CARD_BADGE'),
			'type' => 'text',
			'allowInlineEdit' => false,
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_CARD_TITLE'),
			'type' => 'text',
			'allowInlineEdit' => false,
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_CARD_TEXT'),
			'type' => 'text',
			'allowInlineEdit' => false,
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-node-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_ELEMENT'),
				'type' => ['container-max-width', 'background-color', 'padding-top', 'padding-bottom'],
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-subtitle' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_SUBTITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-tab' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_BADGE'),
				'type' => ['display-element', 'background-color', 'border-color', 'typo'],
			],
			'.landing-block-node-card-box' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_ELEMENT'),
				'type' => ['background-color'],
			],
			'.landing-block-node-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_CARD'),
				'type' => ['border-color', 'background-color', 'border-color-hover', 'background-hover', 'paddings'],
			],
			'.landing-block-node-card-badge' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_CARD_BADGE'),
				'type' => ['display-element', 'background-color', 'color'],
			],
			'.landing-block-node-card-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_CARD_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-card-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_3_NODE_CARD_TEXT'),
				'type' => ['typo'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_carousel'],
	],
];

return $return;