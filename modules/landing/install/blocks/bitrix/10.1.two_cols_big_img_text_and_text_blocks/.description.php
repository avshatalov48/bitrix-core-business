<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NAME'),
		'description' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_DESCRIPTION'),
		'section' => ['text_image', 'about'],
	],
	'cards' => [
		'.landing-block-card-text-block' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_CARDS_LANDINGBLOCKCARDTEXTBLOCK'),
			'label' => ['.landing-block-node-text-block-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => ['height' => 1080],
			'create2xByDefault' => false,
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-text-block-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETEXTBLOCKTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text-block-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETEXTBLOCKTEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-node-texts' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODETEXTS'),
			'type' => 'box',
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		],
		'.landing-block-card-text-block article' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_CARDS_LANDINGBLOCKCARDTEXTBLOCK'),
			'type' => ['box', 'paddings', 'border-colors', 'animation'],
		],
		'.landing-block-node-text-block-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETEXTBLOCKTITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-text-block-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETEXTBLOCKTEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-header' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODEHEADER'),
			'type' => ['text-align', 'heading'],
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'background-size',
		],
	],
];