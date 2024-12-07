<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_27_3_ONE_COL_FIX_TITLE_AND_TEXT_2_NAME_NEW'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['title', 'recommended', 'widgets_text'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_27_3_ONE_COL_FIX_TITLE_AND_TEXT_2_NODES_LANDINGBLOCKNODETITLE'),
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
				'background',
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
				'name' => Loc::getMessage(
					'LANDING_BLOCK_27_3_ONE_COL_FIX_TITLE_AND_TEXT_2_STYLE_LANDINGBLOCKNODETITLE'
				),
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
					//container
					'container-max-width',
					//heading
					'heading-v2',
					'border-color',
					'border-color-hover',
					//other
					'animation',
				],
			],
		],
	],
];