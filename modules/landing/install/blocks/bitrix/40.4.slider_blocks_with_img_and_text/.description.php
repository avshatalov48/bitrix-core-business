<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NAME'),
		'section' => array('tiles'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-img', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDIMG2'),
			'type' => 'img',
			'dimensions' => array('width' => 324),
			'create2xByDefault' => false,
		),
		'.landing-block-node-card-img2' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDIMG2'),
			'type' => 'img',
			'dimensions' => array('width' => 324),
			'create2xByDefault' => false,
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTITLE2'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTEXT2'),
			'type' => 'text',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDBUTTON2'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => ['typo', 'animation', 'heading'],
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => array('typo', 'animation'),
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTITLE2'),
			'type' => 'typo',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTEXT2'),
			'type' => array('typo', 'animation'),
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDBUTTON2'),
			'type' => array('button', 'animation'),
		),
		'.landing-block-node-card-button-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDBUTTON2'),
			'type' => 'text-align',
		),
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.4.SLIDER_BLOCKS_WITH_IMG_AND_TEXT_CARDS_LANDINGBLOCKNODECARD'),
			'type' => array('align-self'),
		),
		'.landing-block-slider' => [
			'additional' => [
				'name' => Loc::getMessage('LANDING_BLOCK_40_4_SLIDER_BLOCKS_WITH_IMG_AND_TEXT_NODES_SLIDER'),
				'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show', 'arrows', 'dots'],
			]
		],
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);