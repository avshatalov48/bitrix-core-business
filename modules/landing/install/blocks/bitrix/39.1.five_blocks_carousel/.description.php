<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NAME'),
		'section' => array('tiles', 'columns'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-img', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDLINK'),
			'type' => 'link',
		),
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 369),
		),
		'.landing-block-node-card-icon1' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDICON'),
			'type' => 'icon',
		),
		'.landing-block-node-card-icon-text1' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDICONTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-icon2' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDICON'),
			'type' => 'icon',
		),
		'.landing-block-node-card-icon-text2' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDICONTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-icon3' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDICON'),
			'type' => 'icon',
		),
		'.landing-block-node-card-icon-text3' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDICONTEXT'),
			'type' => 'text',
		),
	
	),
	'style' => array(
		'block' => [
			//'type' => ['display'],
			'additional' => [
				'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_STYLE_SLIDER'),
				'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show-extended', 'arrows', 'dots'],
			]
		],
		'nodes' => [
			'.landing-block-node-card-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDSUBTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-card-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDLINK'),
				'type' => 'typo-link',
			),
			'.landing-block-node-card-bg' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_STYLE_LANDINGBLOCKNODECARD'),
				'type' => array('box', 'animation'),
			),
			'.landing-block-node-card-icon-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDICON'),
				'type' => ['color', 'font-size'],
			),
			'.landing-block-node-card-texticon-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_5_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDICONTEXT'),
				'type' => ['typo', 'border-color'],
			),
		],
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);