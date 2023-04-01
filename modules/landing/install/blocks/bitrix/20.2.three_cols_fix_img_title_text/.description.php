<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NAME'),
		'section' => ['text_image', 'columns'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NODES_LANDINGBLOCK_CARD'),
			'label' => ['.landing-block-node-img', '.landing-block-node-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => ['width' => 540],
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NODES_LANDINGBLOCK_CARD'),
			'type' => ['columns', 'background-color', 'animation'],
		],
		'.landing-block-inner' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_STYLE_LANDINGBLOCK_INNER'),
			'type' => ['row-align', 'container'],
		],
		'.landing-block-node-img-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NODES_LANDINGBLOCKNODEIMG'),
			'type' => ['text-align'],
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => ['typo', 'heading'],
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => ['typo'],
		],
		'.landing-block-node-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_STYLE_LANDINGBLOCKNODEELEMENT'),
			'type' => ['padding-top', 'padding-bottom', 'container'],
		],
	],
];