<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NAME'),
		'section' => array('tariffs'),
		'type' => ['page', 'store'],
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-icon', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 540),
		),
		'.landing-block-node-card-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-price' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_PRICE'),
			'type' => 'text',
		),
		'.landing-block-node-card-price-list' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARDPRICE_LIST_ITEM'),
			'type' => 'text',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_BUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_CARDS_LANDINGBLOCKNODECARD'),
			'type' => 'columns',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODETITLE'),
			'type' => array('typo', 'border-color'),
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-icon-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_ICON'),
			'type' => ['color', 'background-color'],
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_CONTAINER'),
			'type' => array('box', 'animation'),
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-price' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_PRICE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-price-list li' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARDPRICE_LIST_ITEM'),
			'type' => array('background-color', 'background-gradient'),
		),
		'.landing-block-node-card-price-list' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARDPRICE_LIST'),
			'type' => array('typo'),
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_BUTTON'),
			'type' => 'button',
		),
		'.landing-block-node-card-button-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_NODES_LANDINGBLOCKNODECARD_BUTTON'),
			'type' => 'text-align',
		),
		'.landing-block-inner' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_11.2.THREE_COLS_FIX_TARIFFS_BLACK_CARDS_LANDINGBLOCKNODEINNER'),
			'type' => 'row-align',
		),
	),
);