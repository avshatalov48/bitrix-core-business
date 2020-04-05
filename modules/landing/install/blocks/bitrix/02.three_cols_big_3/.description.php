<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_NAME'),
		'description' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_DESCRIPTION'),
		'section' => array('text_image', 'columns', 'about'),
	),
	'cards' => array(
		'.landing-block-card-left' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_CARDS_LANDINGBLOCKCARDLEFT'),
			'label' => array('.landing-block-node-left-img', '.landing-block-node-left-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-left-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_NODES_LANDINGBLOCKNODELEFTIMG'),
			'type' => 'img',
			'dimensions' => ['width' => 580],
		),
		'.landing-block-node-left-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_NODES_LANDINGBLOCKNODELEFTTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-left-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_NODES_LANDINGBLOCKNODELEFTTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-center-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_NODES_LANDINGBLOCKNODECENTERSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-center-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_NODES_LANDINGBLOCKNODECENTERTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-center-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_NODES_LANDINGBLOCKNODECENTERTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-right-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_NODES_LANDINGBLOCKNODERIGHTIMG'),
			'type' => 'img',
			'dimensions' => ['height' => 1080],
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default'),
		),
		'nodes' => array(
			'.landing-block-node-left' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_STYLE_LANDINGBLOCKNODELEFT'),
				'type' => 'box',
			),
			'.landing-block-node-left-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_STYLE_LANDINGBLOCKNODELEFTTITLE'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-left-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_STYLE_LANDINGBLOCKNODELEFTTEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-center' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_STYLE_LANDINGBLOCKNODECENTER'),
				'type' => 'box',
			),
			'.landing-block-node-center-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_STYLE_LANDINGBLOCKNODECENTERSUBTITLE'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-center-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_STYLE_LANDINGBLOCKNODECENTERTITLE'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-center-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_STYLE_LANDINGBLOCKNODECENTERTEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-header' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_STYLE_LANDINGBLOCKNODEHEADER'),
				'type' => ['border-color', 'text-align'],
			),
			'.landing-block-node-right-img' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_3_NODES_LANDINGBLOCKNODERIGHTIMG'),
				'type' => 'background-size',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);