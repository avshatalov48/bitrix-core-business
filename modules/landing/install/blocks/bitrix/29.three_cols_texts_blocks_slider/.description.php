<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_9_THREE_COLS_TEXTS_BLOCKS_SLIDER_NAME'),
		'section' => array('feedback'),
		'type' => ['page', 'store', 'smn'],
	),
	'cards' => array(
		'.landing-block-card-slider-element' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_THREE_COLS_TEXTS_BLOCKS_SLIDER_CARDS_LANDINGBLOCKCARDSLIDERELEMENT'),
			'label' => array('.landing-block-node-element-img', '.landing-block-node-element-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-element-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_THREE_COLS_TEXTS_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODEELEMENTTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-element-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_THREE_COLS_TEXTS_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODEELEMENTSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-element-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_THREE_COLS_TEXTS_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODEELEMENTTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-element-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_THREE_COLS_TEXTS_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODEELEMENTIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 100, 'height' => 100),
		),
		
		
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default', 'animation'),
		),
		'nodes' => array(
			'.landing-block-node-element-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_9_THREE_COLS_TEXTS_BLOCKS_SLIDER_STYLE_LANDINGBLOCKNODEELEMENTTEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-element-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_9_THREE_COLS_TEXTS_BLOCKS_SLIDER_STYLE_LANDINGBLOCKNODEELEMENTTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-element-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_9_THREE_COLS_TEXTS_BLOCKS_SLIDER_STYLE_LANDINGBLOCKNODEELEMENTSUBTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-element-img' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_9_THREE_COLS_TEXTS_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODEELEMENTIMG'),
				'type' => 'border-radius',
			),
			'.landing-block-card-slider-element' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_9_THREE_COLS_TEXTS_BLOCKS_SLIDER_CARDS_LANDINGBLOCKCARDSLIDERELEMENT'),
				'type' => 'align-self',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);