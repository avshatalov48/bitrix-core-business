<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NAME'),
		'section' => array('image'),
		'dynamic' => false,
//			'subtype' => 'carousel',
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-img'),
		),
	),
	'nodes' => array(
		'.landing-block-node-img1' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODEIMG1'),
			'type' => 'img',
			'dimensions' => array('width' => 960),
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'link',
		),
		'.landing-block-node-img2' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODEIMG2'),
			'type' => 'img',
			'dimensions' => array('width' => 960),
		),
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODECARDIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 960),
		),
	),
	'style' => array(
		'.landing-block-node-block-top' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODE_BLOCK'),
			'type' => 'animation',
		),
		'.landing-block-node-img1' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODEIMG1'),
			'type' => ['background-size', 'animation'],
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'button',
		),
		'.landing-block-node-button-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'text-align',
		),
		'.landing-block-node-img2' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODEIMG2'),
			'type' => ['background-size', 'animation']
		),
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.1.BIG_TILES_WITH_SLIDER_NODES_LANDINGBLOCKNODECARDIMG'),
			'type' => ['background-size', 'animation']
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);