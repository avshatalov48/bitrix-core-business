<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NAME'),
		'description' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_DESCRIPTION'),
		'section' => ['text_image', 'columns', 'about'],
	],
	'cards' => [
		'.landing-block-card-right' => [
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_CARDS_LANDINGBLOCKCARDRIGHT'),
			'label' => ['.landing-block-node-right-img', '.landing-block-node-right-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-left-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODELEFTIMG'),
			'type' => 'img',
			'dimensions' => ['height' => 1080],
			'create2xByDefault' => false,
		],
		'.landing-block-node-center-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODECENTERSUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-center-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODECENTERTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-center-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODECENTERTEXT'),
			'type' => 'text',
		],
		'.landing-block-node-right-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODERIGHTIMG'),
			'type' => 'img',
			'dimensions' => ['width' => 580],
			'create2xByDefault' => false,
		],
		'.landing-block-node-right-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODERIGHTTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-right-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODERIGHTTEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default']
		],
		'nodes' => [
			'.landing-block-node-center-subtitle' => [
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODECENTERSUBTITLE'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-center' => [
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODECENTER'),
				'type' => 'box',
			],
			'.landing-block-node-center-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODECENTERTITLE'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-center-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODECENTERTEXT'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-right-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODERIGHTTITLE'),
				'type' => ['typo', 'animation', 'heading'],
			],
			'.landing-block-node-right' => [
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODERIGHT'),
				'type' => 'box',
			],
			'.landing-block-node-right-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODERIGHTTEXT'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-header' => [
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODEHEADER'),
				'type' => ['text-align', 'heading'],
			],
			'.landing-block-node-left-img' => [
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODELEFTIMG'),
				'type' => 'background-size'
			],
		],
	],
	'assets' => [
		'ext' => ['landing_carousel'],
	],
];