<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_NAME'),
		'section' => array('tariffs'),
		'type' => ['page', 'store', 'smn'],
	),
	'cards' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_NODES_LANDINGBLOCK_CARD'),
			'label' => array('.landing-block-node-title', '.landing-block-node-subtitle'),
		),
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-price' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_NODES_LANDINGBLOCKNODEPRICE'),
			'type' => 'text',
		),
		'.landing-block-node-price-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_NODES_LANDINGBLOCKNODEPRICETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-price-list' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_STYLE_LANDINGBLOCKNODEPRICELISTITEM'),
			'type' => 'text',
		),
		'.landing-block-node-price-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_NODES_LANDINGBLOCKNODEPRICEBUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_NODES_LANDINGBLOCK_CARD'),
			'type' => array('columns', 'animation'),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_STYLE_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-price' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_STYLE_LANDINGBLOCKNODEPRICE'),
			'type' => 'typo',
		),
		'.landing-block-node-price-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_STYLE_LANDINGBLOCKNODEPRICETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-price-list-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_STYLE_LANDINGBLOCKNODEPRICELISTITEM'),
			'type' => 'typo',
		),
		'.landing-block-node-price-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_NODES_LANDINGBLOCKNODEPRICEBUTTON'),
			'type' => 'button',
		),
		'.landing-block-card-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_NODES_LANDINGBLOCKNODEPRICECONTAINER'),
			'type' => array('box', 'paddings'),
		),
		'.landing-block-node-price-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_NODES_LANDINGBLOCKNODEPRICEBUTTON'),
			'type' => array('text-align'),
		),
		'.landing-block-inner' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_THREE_COLS_FIX_TARIFFS_NODES_LANDINGBLOCK_INNER'),
			'type' => array('row-align'),
		),
	),
);