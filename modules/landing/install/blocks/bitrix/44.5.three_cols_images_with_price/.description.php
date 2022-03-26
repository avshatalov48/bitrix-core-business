<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_NAME'),
		'section' => array('image'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-bgimg', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-price' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_NODES_LANDINGBLOCKNODECARDPRICE'),
			'type' => 'text',
		),
		'.landing-block-node-card-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_NODES_LANDINGBLOCKNODECARDBGIMG'),
			'type' => 'img',
			'useInDesigner' => false,
			'dimensions' => array('width' => 540),
			'create2xByDefault' => false,
		),
	),
	'style' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_CARDS_LANDINGBLOCKNODECARD'),
			'type' => array('columns', 'animation'),
		),
		'.landing-block-node-card-bg-hover' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_NODES_CARD_BACKGROUND_HOVER'),
			'type' => array('bg'),
		),
		'.landing-block-node-card-price' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_NODES_LANDINGBLOCKNODECARDPRICE'),
			'type' => array('typo', 'box'),
		),
		'.landing-block-node-card-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.5.THREE_COLS_IMAGES_WITH_PRICE_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'typo',
		),
	),
);