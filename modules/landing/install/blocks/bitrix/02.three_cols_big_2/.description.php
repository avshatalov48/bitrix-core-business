<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NAME'),
		'description' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_DESCRIPTION'),
		'section' => ['text_image', 'columns', 'about'],
	],
	'cards' => [
		'.landing-block-card-left' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_CARDS_LANDINGBLOCKCARD_LEFT'),
			'label' => ['.landing-block-node-img-left', '.landing-block-node-carousel-element-title'],
		],
		'.landing-block-card-right' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_CARDS_LANDINGBLOCKCARD_RIGHT'),
			'label' => ['.landing-block-node-img-right', '.landing-block-node-carousel-element-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-img-left' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => ['width' => 580],
		],
		'.landing-block-node-img-right' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => ['width' => 580],
		],
		'.landing-block-node-carousel-element-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODECAROUSELELEMENTTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-carousel-element-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODECAROUSELELEMENTTEXT'),
			'type' => 'text',
		],
		'.landing-block-node-center-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODECENTERSUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-center-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODECENTERTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-center-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODECENTERTEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-background'],
		],
		'nodes' => [
			'.landing-block-node-left' => [
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODE_LEFT_COLUMN'),
				'type' => 'box',
				'additional' => [
					'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODE_SLIDER'),
					'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show', 'dots'],
				]
			],
			'.landing-block-node-right' => [
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODE_LEFT_COLUMN'),
				'type' => 'box',
				'additional' => [
					'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODE_SLIDER'),
					'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show', 'dots'],
				]
			],
			'.landing-block-node-carousel-element-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODECAROUSELELEMENTTITLE'),
				'type' => ['typo', 'animation', 'heading'],
			],
			'.landing-block-node-carousel-element-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODECAROUSELELEMENTTEXT'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-center' => [
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODECENTER'),
				'type' => 'box',
			],
			'.landing-block-node-center-subtitle' => [
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODECENTERSUBTITLE'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-center-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODECENTERTITLE'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-center-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODECENTERTEXT'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-header' => [
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODEHEADER'),
				'type' => ['heading'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_carousel'],
	],
];