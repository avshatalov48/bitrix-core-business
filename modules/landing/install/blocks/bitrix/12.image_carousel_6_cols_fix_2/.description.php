<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_6_IMAGE_CAROUSEL_6_COLS_FIX_2_NAME'),
		'section' => array('image', 'partners'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-card-carousel-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_IMAGE_CAROUSEL_6_COLS_FIX_2_CARDS_LANDINGBLOCKCARDCAROUSELITEM'),
			'label' => array('.landing-block-node-carousel-img'),
		),
	),
	'nodes' => array(
		'.landing-block-node-carousel-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_IMAGE_CAROUSEL_6_COLS_FIX_2_NODES_LANDINGBLOCKNODECAROUSELIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 200),
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default', 'animation'),
		),
		'nodes' => array(
			'.landing-block-card-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_6_IMAGE_CAROUSEL_6_COLS_FIX_2_CARDS_LANDINGBLOCKCARDCAROUSELITEM'),
				'type' => ['row-align-column', 'align-items-column']
			),
			'.landing-block-slider' => [
				'additional' => [
					'name' => Loc::getMessage('LANDING_BLOCK_6_IMAGE_CAROUSEL_6_COLS_FIX_2_NODES_SLIDER'),
					'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show-extended', 'arrows', 'dots'],
				]
			],
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);