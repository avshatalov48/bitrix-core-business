<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_32.7.IMG_GRID_4_COLS_2_NAME'),
		'section' => array('image'),
		'dynamic' => false,
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-img-big' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.7.IMG_GRID_4_COLS_2_NODES_LANDINGBLOCKNODEIMG1'),
			'type' => 'img',
			'dimensions' => array('width' => 1080),
			'allowInlineEdit' => false,
		),
		'.landing-block-node-img-small' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.7.IMG_GRID_4_COLS_2_NODES_LANDINGBLOCKNODEIMG2'),
			'type' => 'img',
			'dimensions' => array('width' => 1080),
			'allowInlineEdit' => false,
		),
		'.landing-block-node-img-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.7.IMG_GRID_4_COLS_2_NODES_LANDINGBLOCKNODETXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-img-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.7.IMG_GRID_4_COLS_2_NODES_LANDINGBLOCKNODETXT'),
			'type' => 'typo',
		),
		'.landing-block-node-img-container-left' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.7.IMG_GRID_4_COLS_2_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'animation',
		),
		'.landing-block-node-img-container-center-top' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.7.IMG_GRID_4_COLS_2_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'animation',
		),
		'.landing-block-node-img-container-center-bottom' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.7.IMG_GRID_4_COLS_2_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'animation',
		),
		'.landing-block-node-img-container-right-top' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.7.IMG_GRID_4_COLS_2_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'animation',
		),
		'.landing-block-node-img-container-right-bottom' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.7.IMG_GRID_4_COLS_2_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'animation',
		),
	),
	'assets' => array(
		'ext' => array('landing_gallery_cards'),
	),
);