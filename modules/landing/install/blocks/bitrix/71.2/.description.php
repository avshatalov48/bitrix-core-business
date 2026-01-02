<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_2_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['columns', 'widgets_columns'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_2_NODE_CARD'),
			'label' => ['.landing-block-node-card-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_2_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_2_NODE_CARD_ICON'),
			'type' => 'icon',
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_2_NODE_CARD_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_2_NODE_CARD_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_2_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_2_NODE_CARD'),
				'type' => ['background-color', 'background-hover'],
			],
			'.landing-block-node-card-badge' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_2_NODE_CARD_ELEMENT'),
				'type' => ['background-color', 'color'],
			],
			'.landing-block-node-card-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_2_NODE_CARD_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-card-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_2_NODE_CARD_TEXT'),
				'type' => ['typo'],
			],
		],
	],
];

return $return;