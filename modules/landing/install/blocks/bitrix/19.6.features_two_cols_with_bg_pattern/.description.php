<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NAME'),
			'section' => array('text_image', 'about'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NODES_LANDINGBLOCKNODE_CARD'),
				'label' => array('.landing-block-node-card-img', '.landing-block-node-card-title')
			),
		),
	'nodes' =>
		array(
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NODES_LANDINGBLOCKNODETITLE'),
				'type' => 'text',
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NODES_LANDINGBLOCKNODETEXT'),
				'type' => 'text',
			),
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NODES_LANDINGBLOCKNODECARDTITLE2'),
				'type' => 'text',
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NODES_LANDINGBLOCKNODECARDTEXT2'),
				'type' => 'text',
			),
			'.landing-block-node-card-img' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NODES_LANDINGBLOCKNODECARDIMG2'),
				'type' => 'img',
				'dimensions' => array('width' => 169, 'height' => 169),
			),
		),
	'style' =>
		array(
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NODES_LANDINGBLOCKNODETITLE'),
				'type' => array('typo', 'border-color'),
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NODES_LANDINGBLOCKNODETEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NODES_LANDINGBLOCKNODE_CARD'),
				'type' => 'animation',
			),
			'.landing-block-node-card-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NODES_LANDINGBLOCKNODE_CARD'),
				'type' => array('bg'),
			),
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NODES_LANDINGBLOCKNODECARDTITLE2'),
				'type' => 'typo',
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_19.6.FEATURES_TWO_COLS_WITH_BG_PATTERN_NODES_LANDINGBLOCKNODECARDTEXT2'),
				'type' => 'typo',
			),
		),
);