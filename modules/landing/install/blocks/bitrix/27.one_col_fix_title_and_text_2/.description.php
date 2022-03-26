<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TITLE_AND_TEXT_2_NAME_NEW'),
		'section' => ['title', 'text', 'recommended'],
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
			'type' => ['block-default', 'block-border', 'animation'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TITLE_AND_TEXT_2_STYLE_LANDINGBLOCKNODETITLE'),
				'type' => ['typo', 'heading', 'animation'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TITLE_AND_TEXT_2_STYLE_LANDINGBLOCKNODETEXT'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-text-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TITLE_AND_TEXT_2_STYLE_LANDINGBLOCKNODETEXT'),
				'type' => ['container'],
			],
		],
	],
];