<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NAME'),
		'section' => ['text_image', 'about'],
	],
	'cards' => [
		'.landing-block-card-carousel-element' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_CARDS_LANDINGBLOCKCARDCAROUSELELEMENT'),
			'label' => ['.landing-block-node-carousel-element-img', '.landing-block-node-carousel-element-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-carousel-element-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODECAROUSELELEMENTIMG'),
			'type' => 'img',
			'useInDesigner' => false,
			'dimensions' => ['width' => 355],
		],
		'.landing-block-node-carousel-element-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODECAROUSELTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-carousel-element-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODECAROUSELTEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-node-text-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => ['animation', 'align-items'],
		],
		'.landing-block-node-carousel-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_CARDS_LANDINGBLOCKCARDCAROUSEL'),
			'type' => 'animation',
			'additional' => [
				'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODE_SLIDER'),
				'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show-extended', 'arrows', 'dots'],
			]
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-carousel-element-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODECAROUSELTITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-carousel-element-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODECAROUSELTEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-carousel-element-img-hover' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODECAROUSELELEMENTIMGHOVER'),
			'type' => ['background-color'],
		],
		'.landing-block-node-header' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_TWO_COLS_FIX_TEXT_AND_IMAGE_SLIDER_STYLE_LANDINGBLOCKNODEHEADER'),
			'type' => ['text-align', 'heading'],
		],
	],
	'assets' => [
		'ext' => ['landing_carousel'],
	],
];