<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NAME'),
		'description' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_DESCRIPTION'),
		'section' => array('text_image', 'columns', 'about'),
	),
	'cards' => array(
		'.landing-block-card-right' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_CARDS_LANDINGBLOCKCARDRIGHT'),
			'label' => array('.landing-block-node-right-img', '.landing-block-node-right-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-left-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODELEFTIMG'),
			'type' => 'img',
			'dimensions' => array('height' => 1080),
		),
		'.landing-block-node-center-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODECENTERSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-center-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODECENTERTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-center-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODECENTERTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-right-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODERIGHTIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 580),
		),
		'.landing-block-node-right-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODERIGHTTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-right-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODERIGHTTEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default')
		),
		'nodes' => array(
			'.landing-block-node-center-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODECENTERSUBTITLE'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-center' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODECENTER'),
				'type' => 'box',
			),
			'.landing-block-node-center-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODECENTERTITLE'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-center-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODECENTERTEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-right-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODERIGHTTITLE'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-right' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODERIGHT'),
				'type' => 'box',
			),
			'.landing-block-node-right-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODERIGHTTEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-header' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_STYLE_LANDINGBLOCKNODEHEADER'),
				'type' => ['border-color', 'text-align'],
			),
			'.landing-block-node-left-img' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_THREE_COLS_1_NODES_LANDINGBLOCKNODELEFTIMG'),
				'type' => 'background-size'
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);