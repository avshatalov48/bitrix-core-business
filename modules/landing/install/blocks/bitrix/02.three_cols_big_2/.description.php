<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NAME'),
		'description' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_DESCRIPTION'),
		'section' => array('text_image', 'columns', 'about'),
	),
	'cards' => array(
		'.landing-block-card-left' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_CARDS_LANDINGBLOCKCARD_LEFT'),
			'label' => array('.landing-block-node-carousel-element-title'),
		),
		'.landing-block-card-right' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_CARDS_LANDINGBLOCKCARD_RIGHT'),
			'label' => array('.landing-block-node-carousel-element-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1600, 'height' => 1600),
		),
		'.landing-block-node-carousel-element-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODECAROUSELELEMENTTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-carousel-element-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODECAROUSELELEMENTTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-center-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODECENTERSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-center-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODECENTERTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-center-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_NODES_LANDINGBLOCKNODECENTERTEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-wo-background'),
		),
		'nodes' => array(
			'.landing-block-node-left' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODE_LEFT_COLUMN'),
				'type' => 'box',
			),
			'.landing-block-node-right' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODE_LEFT_COLUMN'),
				'type' => 'box',
			),
			'.landing-block-node-carousel-element-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODECAROUSELELEMENTTITLE'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-carousel-element-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODECAROUSELELEMENTTEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-center' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODECENTER'),
				'type' => 'box',
			),
			'.landing-block-node-center-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODECENTERSUBTITLE'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-center-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODECENTERTITLE'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-center-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODECENTERTEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-header' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_2_STYLE_LANDINGBLOCKNODEHEADER'),
				'type' => 'border-color',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);