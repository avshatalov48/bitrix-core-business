<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_31.3.TWO_COLS_TEXT_IMG_FIX_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['text_image', 'recommended', 'widgets_image'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31.3.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31.3.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31.3.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => ['width' => 540],
		],
	],
	'style' => [
		'block' => [
			'type' => [
				//block-default
				'display',
				'background',
				'padding-top',
				'padding-bottom',
				'padding-left',
				'padding-right',
				'margin-top',
				//block-border
				'block-border-type',
				'block-border-margin',
				'border-radius',
				'block-border-position',
			],
		],
		'nodes' => [
			'.landing-block-node-text-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_31.3.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODETEXT'),
				'type' => ['animation'],
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_31.3.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODETITLE'),
				'type' => [
					//typo
					'text-align',
					'color',
					'font-size',
					'font-family',
					'font-weight',
					'text-decoration',
					'text-transform',
					'line-height',
					'letter-spacing',
					'word-break',
					'text-shadow',
					'padding-top',
					'padding-left',
					'padding-right',
					'margin-bottom',
					//heading
					'heading-v2',
					'border-color',
					'border-color-hover',
				],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_31.3.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODETEXT'),
				'type' => [
					//typo
					'text-align',
					'color',
					'font-size',
					'font-family',
					'font-weight',
					'text-decoration',
					'text-transform',
					'line-height',
					'letter-spacing',
					'word-break',
					'text-shadow',
					'padding-top',
					'padding-left',
					'padding-right',
					'margin-bottom',
				],
			],
			'.landing-block-node-img' => [
				'name' => Loc::getMessage('LANDING_BLOCK_31.3.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODEIMG'),
				'type' => 'animation',
			],
			'.landing-block-node-block' => [
				'name' => Loc::getMessage('LANDING_BLOCK_31.3.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODEBLOCK'),
				'type' => 'align-items',
			],
			'.landing-block-node-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_31.3.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODE_ELEMENT'),
				'type' => [
					//container
					'container-max-width',
					'padding-left',
					'padding-right',
					//other
					'padding-top',
					'padding-bottom',
				],
			],
		],
	],
];