<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NAME'),
		'section' => array('text_image', 'about'),
	),
	'cards' => array(
		'.landing-block-card-carousel-element' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_CARDS_LANDINGBLOCKCARDCAROUSELELEMENT'),
			'label' => array('.landing-block-node-carousel-element-img', '.landing-block-node-carousel-element-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-carousel-element-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODECAROUSELELEMENTIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 355),
		),
		'.landing-block-node-carousel-element-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODECAROUSELTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-carousel-element-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODECAROUSELTEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-text-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => array('animation', 'align-items'),
		),
		'.landing-block-node-carousel-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_CARDS_LANDINGBLOCKCARDCAROUSEL'),
			'type' => 'animation',
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-carousel-element-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODECAROUSELTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-carousel-element-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODECAROUSELTEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-carousel-element-img-hover' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODECAROUSELELEMENTIMGHOVER'),
			'type' => array('background-color', 'background-gradient'),
		),
		'.landing-block-node-header' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODEHEADER'),
			'type' => 'border-color',
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);