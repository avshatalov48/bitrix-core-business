<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_27_4_ONE_COL_FIX_TEXT_NAME_NEW'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['text', 'recommended', 'widgets_text'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_27_4_ONE_COL_FIX_TEXT_NODES_LANDINGBLOCKNODE_TEXT'),
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
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_27_4_ONE_COL_FIX_TEXT_NODES_LANDINGBLOCKNODE_TEXT'),
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
					//other
					'animation',
				],
			],
		],
	],
];