<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NAME'),
		'section' => ['tariffs'],
		'type' => ['page', 'store'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_CARDS_LANDINGBLOCKNODECARD'),
			'label' => ['.landing-block-node-card-icon', '.landing-block-node-card-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_IMG'),
			'type' => 'img',
			'dimensions' => ['width' => 540],
		],
		'.landing-block-node-card-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_ICON'),
			'type' => 'icon',
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-price' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_PRICE'),
			'type' => 'text',
		],
		'.landing-block-node-card-price-list' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARDPRICE_LIST_ITEM'),
			'type' => 'text',
		],
		'.landing-block-node-card-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_BUTTON'),
			'type' => 'link',
		],
	],
	'style' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_CARDS_LANDINGBLOCKNODECARD'),
			'type' => ['columns', 'margin-bottom'],
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODETITLE'),
			'type' => ['typo', 'heading'],
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-card-icon-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_ICON'),
			'type' => ['color', 'background-color'],
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => ['typo'],
		],
		'.landing-block-node-card-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_CONTAINER'),
			'type' => ['box', 'animation'],
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-card-price' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_PRICE'),
			'type' => 'typo',
		],
		'.landing-block-node-card-price-list li' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARDPRICE_LIST_ITEM'),
			'type' => ['background-color'],
		],
		'.landing-block-node-card-price-list' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARDPRICE_LIST'),
			'type' => ['typo'],
		],
		'.landing-block-node-card-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_BUTTON'),
			'type' => 'button',
		],
		'.landing-block-node-card-button-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_BUTTON'),
			'type' => 'text-align',
		],
		'.landing-block-inner' => [
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_CARDS_LANDINGBLOCKNODEINNER'),
			'type' => 'row-align',
		],
	],
];