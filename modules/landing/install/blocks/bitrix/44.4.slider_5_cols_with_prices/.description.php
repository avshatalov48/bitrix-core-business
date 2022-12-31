<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_44.4.SLIDER_5_COLS_WITH_PRICES_NAME'),
		'section' => array('columns'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.4.SLIDER_5_COLS_WITH_PRICES_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-img', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.4.SLIDER_5_COLS_WITH_PRICES_NODES_LANDINGBLOCKNODECARDIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 360),
			'create2xByDefault' => false,
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.4.SLIDER_5_COLS_WITH_PRICES_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.4.SLIDER_5_COLS_WITH_PRICES_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-price' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.4.SLIDER_5_COLS_WITH_PRICES_NODES_LANDINGBLOCKNODECARDPRICE'),
			'type' => 'text',
		),
	),
	'style' => [
		'block' => [
			'type' => ['block-default-background-overlay', 'background'],
			'additional' => [
				'name' => Loc::getMessage('LANDING_BLOCK_44_4_SLIDER_5_COLS_WITH_PRICES_NODES_LANDINGBLOCKNODE_SLIDER'),
				'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show-extended', 'arrows', 'dots'],
			]
		],
		'nodes' => [
			'.landing-block-node-card-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_44.4.SLIDER_5_COLS_WITH_PRICES_CARDS_LANDINGBLOCKNODECARD'),
				'type' => array('background-color', 'background-hover'),
			),
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_44.4.SLIDER_5_COLS_WITH_PRICES_NODES_LANDINGBLOCKNODECARDTITLE'),
				'type' => ['typo', 'animation', 'heading'],
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_44.4.SLIDER_5_COLS_WITH_PRICES_NODES_LANDINGBLOCKNODECARDTEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-card-price' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_44.4.SLIDER_5_COLS_WITH_PRICES_NODES_LANDINGBLOCKNODECARDPRICE'),
				'type' => array('typo', 'box', 'animation', 'color-hover', 'background-color-hover'),
			),
		],
	],
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);