<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TEXTS_BLOCKS_SLIDER_NAME'),
		'section' => array('tiles'),
	),
	'cards' => array(
		'.landing-block-card-slider-element' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TEXTS_BLOCKS_SLIDER_CARDS_LANDINGBLOCKCARDSLIDERELEMENT'),
			'label' => array('.landing-block-node-element-title'),
		),
	),
	'nodes' => array(
		
		'.landing-block-node_bgimage' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TEXTS_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODE_BGIMAGE'),
			'type' => 'img',
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'allowInlineEdit' => false,
		),
		'.landing-block-node-element-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TEXTS_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODEELEMENTTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-element-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TEXTS_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODEELEMENTTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-element-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TEXTS_BLOCKS_SLIDER_NODES_BLOCKNODEELEMENTBUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-wo-background'),
		),
		'nodes' => array(
			'.landing-block-card-slider-element' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TEXTS_BLOCKS_SLIDER_CARDS_LANDINGBLOCKCARDSLIDERELEMENT'),
				'type' => array('align-self'),
			),
			'.landing-block-node-element-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TEXTS_BLOCKS_SLIDER_STYLE_LANDINGBLOCKNODEELEMENTTITLE'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-element-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TEXTS_BLOCKS_SLIDER_STYLE_LANDINGBLOCKNODEELEMENTTEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-element-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TEXTS_BLOCKS_SLIDER_NODES_BLOCKNODEELEMENTBUTTON'),
				'type' => array('button', 'animation'),
			),
			'.landing-block-node_bgimage' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TEXTS_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODE_BGIMAGE'),
				'type' => 'background-overlay',
			),
			'.landing-block-node-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_9_ONE_COL_FIX_TEXTS_BLOCKS_SLIDER_NODES_BLOCKNODEELEMENTBUTTON'),
				'type' => 'text-align',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);