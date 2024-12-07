<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['video', 'widgets_video'],
		'dynamic' => false,
		'version' => '18.5.0',
		// old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-video' => [
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODEVIDEO'),
			'type' => 'embed',
		],
	],
	'style' => [
		'.landing-block-node-text-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETEXT'),
			'type' => ['animation', 'align-items'],
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETITLE'),
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
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETEXT'),
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
		'.landing-block-node-video-col' => [
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODEVIDEO'),
			'type' => ['align-self', 'animation'],
		],
		'.landing-block-node-video-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODEVIDEO'),
			'type' => ['orientation', 'video-scale'],
		],
	],
	'assets' => [
		'ext' => ['landing_inline_video'],
	],
];