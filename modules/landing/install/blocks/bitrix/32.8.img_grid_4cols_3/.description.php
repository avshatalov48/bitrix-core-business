<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.8.IMG_GRID_4_COLS_3_NAME'),
			'section' => array('image'),
		),
	'cards' => array(),
	'nodes' =>
		array(
			'.landing-block-node-img-small' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_32.8.IMG_GRID_4_COLS_3_NODES_LANDINGBLOCKNODEIMG1'),
					'type' => 'img',
					'dimensions' => array('width' => 960, 'height' => 960),
					'allowInlineEdit' => false,
				),
			'.landing-block-node-img-big' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_32.8.IMG_GRID_4_COLS_3_NODES_LANDINGBLOCKNODEIMG2'),
					'type' => 'img',
					'dimensions' => array('width' => 1440, 'height' => 960),
					'allowInlineEdit' => false,
				),
			'.landing-block-node-img-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_32.8.IMG_GRID_4_COLS_3_NODES_LANDINGBLOCKNODETXT'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'.landing-block-node-img-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_32.8.IMG_GRID_4_COLS_3_NODES_LANDINGBLOCKNODETXT'),
				'type' => 'typo',
			),
			'.landing-block-node-img-container-left-top' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_32.8.IMG_GRID_4_COLS_3_NODES_LANDINGBLOCKNODEIMG'),
				'type' => 'animation',
			),
			'.landing-block-node-img-container-left-bottom' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_32.8.IMG_GRID_4_COLS_3_NODES_LANDINGBLOCKNODEIMG'),
				'type' => 'animation',
			),
			'.landing-block-node-img-container-center' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_32.8.IMG_GRID_4_COLS_3_NODES_LANDINGBLOCKNODEIMG'),
				'type' => 'animation',
			),
			'.landing-block-node-img-container-right-top' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_32.8.IMG_GRID_4_COLS_3_NODES_LANDINGBLOCKNODEIMG'),
				'type' => 'animation',
			),
			'.landing-block-node-img-container-right-bottom' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_32.8.IMG_GRID_4_COLS_3_NODES_LANDINGBLOCKNODEIMG'),
				'type' => 'animation',
			),
		),
	'assets' => array(
		'ext' => array('landing_gallery_cards'),
	),
);