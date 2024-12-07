<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_CVR003._NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['cover', 'widgets_image'],
		'dynamic' => false,
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_CVR003._NODES_LANDINGBLOCKNODE_IMG'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'dimensions' => ['width' => 1920, 'height' => 1080],
			'create2xByDefault' => false,
			'isWrapper' => true,
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_CVR003._NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_CVR003._NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_CVR003._NODES_LANDINGBLOCKNODE_BUTTON'),
			'type' => 'link',
		],
	],
	'style' => [
		'block' => [
			'type' => [
				//block-default-background-height-vh
				'display',
				'background',
				'height-vh',
				'padding-top',
				'padding-bottom',
				'padding-left',
				'padding-right',
				'margin-top',
			],
		],
		'nodes' => [
			'.landing-block-node-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_CVR003._NODES_LANDINGBLOCKNODE_CONTAINER'),
				'type' => ['text-align', 'animation'],
			],
			'.landing-block-node-button-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_CVR003._NODES_LANDINGBLOCKNODE_BUTTON'),
				'type' => 'text-align',
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_CVR003._NODES_LANDINGBLOCKNODETITLE'),
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
					'text-align',
					'heading-v2',
					'border-color',
					'border-color-hover',
					'margin-bottom',
				],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_CVR003._NODES_LANDINGBLOCKNODETEXT'),
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
				]
			],
			'.landing-block-node-button' => [
				'name' => Loc::getMessage('LANDING_BLOCK_CVR003._NODES_LANDINGBLOCKNODE_BUTTON'),
				'type' => 'button',
			],
		],
	],
];