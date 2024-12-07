<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TITLE_AND_TEXT_2_NAME_NEW'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['title', 'text', 'recommended', 'widgets_text'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TITLE_AND_TEXT_2_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TITLE_AND_TEXT_2_NODES_LANDINGBLOCKNODETEXT'),
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
				//block-border
				'block-border-type',
				'block-border-margin',
				'border-radius',
				'block-border-position',
				//other
				'animation',
			],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TITLE_AND_TEXT_2_STYLE_LANDINGBLOCKNODETITLE'),
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
					//other
					'animation',
				],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TITLE_AND_TEXT_2_STYLE_LANDINGBLOCKNODETEXT'),
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
					//other
					'animation',
				],
			],
			'.landing-block-node-text-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TITLE_AND_TEXT_2_STYLE_LANDINGBLOCKNODETEXT'),
				'type' => [
					//container
					'container-max-width',
					'padding-left',
					'padding-right',
				],
			],
		],
	],
];