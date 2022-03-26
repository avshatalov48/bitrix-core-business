<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_31.1.TWO_COLS_TEXT_IMG_NAME'),
		'section' => array('tiles'),
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31.1.TWO_COLS_TEXT_IMG_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('height' => 1080),
			'create2xByDefault' => false,
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31.1.TWO_COLS_TEXT_IMG_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31.1.TWO_COLS_TEXT_IMG_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31.1.TWO_COLS_TEXT_IMG_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31.1.TWO_COLS_TEXT_IMG_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => ['typo', 'animation', 'heading'],
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31.1.TWO_COLS_TEXT_IMG_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => array('typo', 'animation'),
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31.1.TWO_COLS_TEXT_IMG_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => array('button', 'animation'),
		),
		'.landing-block-node-button-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31.1.TWO_COLS_TEXT_IMG_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => array('text-align'),
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31.1.TWO_COLS_TEXT_IMG_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'background-size',
		),
	),
);