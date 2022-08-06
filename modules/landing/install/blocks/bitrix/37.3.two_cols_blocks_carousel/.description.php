<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_NAME_NEW'),
		'section' => array('tiles'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-bgimg', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDBGIMG'),
			'type' => 'img',
			'dimensions' => array('height' => 1080),
			'create2xByDefault' => false,
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-label-left' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARD_LABEL_LEFT'),
			'type' => 'text',
		),
		'.landing-block-node-card-label-right' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARD_LABEL_RIGHT'),
			'type' => 'text',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'link',
		),
	
	),
	'style' => array(
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDBUTTON'),
			'type' => array('button', 'border-color', 'animation'),
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => ['typo', 'animation', 'heading'],
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDTEXT'),
			'type' => array('typo', 'animation'),
		),
		'.landing-block-node-card-text-bg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDTEXTBG'),
			'type' => array('box', 'paddings'),
		),
		'.landing-block-node-card-label-left' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARD_LABEL_LEFT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-label-right' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.3.TWO_COLS_BLOCKS_CAROUSEL_NODES_LANDINGBLOCKNODECARD_LABEL_RIGHT'),
			'type' => 'typo',
		),
		'.landing-block-slider' => [
			'additional' => [
				'name' => Loc::getMessage('LANDING_BLOCK_37_3_TWO_COLS_BLOCKS_CAROUSEL_STYLE_SLIDER'),
				'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show', 'arrows', 'dots'],
			]
		],
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);