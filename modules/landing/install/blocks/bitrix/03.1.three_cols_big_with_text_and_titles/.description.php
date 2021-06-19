<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NAME'),
		'description' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_DESCRIPTION'),
		'section' => ['columns', 'text'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NODES_LANDINGBLOCK_CARD'),
			'label' => [
				'.landing-block-node-subtitle',
				'.landing-block-node-title',
			],
		],
	],
	'nodes' => [
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		
		//			to updater fixes
		//			'.landing-block-inner-container' => array(),
	],
	'style' => [
		'.landing-block-inner-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NODES_LANDINGBLOCK_COLS'),
			'type' => ['row-align'],
		],
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NODES_LANDINGBLOCK_CARD'),
			'type' => ['columns', 'animation'],
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_STYLE_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-card-header' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_STYLE_LANDINGBLOCKNODECARDHEADER'),
			'type' => ['text-align', 'heading'],
		],
	],
];