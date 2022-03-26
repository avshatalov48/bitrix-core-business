<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_FIX_WITH_TITLE_AND_TEXT_NAME'),
		'section' => ['title'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_FIX_WITH_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_FIX_WITH_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_FIX_WITH_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'animation'],
		],
		'nodes' => [
			'.landing-block-node-subtitle' => [
				'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_FIX_WITH_TITLE_AND_TEXT_STYLE_LANDINGBLOCKNODESUBTITLE'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_FIX_WITH_TITLE_AND_TEXT_STYLE_LANDINGBLOCKNODETITLE'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_FIX_WITH_TITLE_AND_TEXT_STYLE_LANDINGBLOCKNODETEXT'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-inner' => [
				'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_FIX_WITH_TITLE_AND_TEXT_STYLE_LANDINGBLOCKNODEINNER'),
				'type' => ['text-align', 'heading', 'animation'],
			],
			'.landing-block-node-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_FIX_WITH_TITLE_AND_TEXT_STYLE_LANDINGBLOCKNODEINNER'),
				'type' => ['container'],
			],
		],
	],
];