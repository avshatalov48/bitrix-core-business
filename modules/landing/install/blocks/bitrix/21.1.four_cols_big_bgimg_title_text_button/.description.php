<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_7_FOUR_COLS_BIG_BGIMG_TITLE_TEXT_BUTTON_NAME'),
		'section' => array('tiles', 'news'),
	),
	'cards' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_FOUR_COLS_BIG_BGIMG_TITLE_TEXT_BUTTON_NODES_LANDINGBLOCK_CARD'),
			'label' => array('.landing-block-node-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_FOUR_COLS_BIG_BGIMG_TITLE_TEXT_BUTTON_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 960),
			'create2xByDefault' => false,
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_FOUR_COLS_BIG_BGIMG_TITLE_TEXT_BUTTON_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_FOUR_COLS_BIG_BGIMG_TITLE_TEXT_BUTTON_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_FOUR_COLS_BIG_BGIMG_TITLE_TEXT_BUTTON_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-wo-background'),
		),
		'nodes' => array(
			'.landing-block-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_7_FOUR_COLS_BIG_BGIMG_TITLE_TEXT_BUTTON_NODES_LANDINGBLOCK_CARD'),
				'type' => array('columns', 'background-overlay', 'animation'),
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_7_FOUR_COLS_BIG_BGIMG_TITLE_TEXT_BUTTON_STYLE_LANDINGBLOCKNODETITLE'),
				'type' => ['typo', 'animation', 'heading'],
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_7_FOUR_COLS_BIG_BGIMG_TITLE_TEXT_BUTTON_STYLE_LANDINGBLOCKNODETEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_7_FOUR_COLS_BIG_BGIMG_TITLE_TEXT_BUTTON_NODES_LANDINGBLOCKNODEBUTTON'),
				'type' => array('button', 'animation'),
			),
			'.landing-block-node-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_7_FOUR_COLS_BIG_BGIMG_TITLE_TEXT_BUTTON_NODES_LANDINGBLOCKNODEBUTTON'),
				'type' => 'text-align',
			),
			'.landing-block-inner' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_7_FOUR_COLS_BIG_BGIMG_TITLE_TEXT_BUTTON_NODES_LANDINGBLOCK_INNER'),
				'type' => 'row-align',
			),
		),
	),
);