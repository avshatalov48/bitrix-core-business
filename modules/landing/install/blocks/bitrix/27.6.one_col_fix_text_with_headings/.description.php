<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_27_6_ONE_COL_FIX_TEXT_HEADINGS_NAME_NEW'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['text', 'widgets_text'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_27_6_ONE_COL_FIX_TEXT_HEADINGS_NODES_LANDINGBLOCKNODE_TEXT'),
			'type' => 'text',
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
				//other
				'animation',
			],
		],
		'nodes' => [
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_27_6_ONE_COL_FIX_TEXT_HEADINGS_NODES_LANDINGBLOCKNODE_TEXT'),
				'type' => [
					//container
					'container-max-width',
					'padding-left',
					'padding-right',
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
					//other
					'animation',
				],
			],
			'.landing-block-node-text-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_27_6_ONE_COL_FIX_TEXT_HEADINGS_NODES_LANDINGBLOCKNODE_TEXT'),
				'type' => [
					//container
					'container-max-width',
					'padding-left',
					'padding-right',
					//heading
					'text-align',
					'heading-v2',
					'border-color',
					'border-color-hover',
					'margin-bottom',
				],
			],
		],
	],
];