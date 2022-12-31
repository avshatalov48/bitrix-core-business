<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_9_IMAGE_CAROUSEL_6_COLS_FIX_3_2_NAME'),
		'section' => array('partners'),
		'dynamic' => false,
		'type' => ['page', 'store', 'smn'],
	),
	'cards' => array(
		'.landing-block-card-carousel-element' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_IMAGE_CAROUSEL_6_COLS_FIX_3_2_CARDS_LANDINGBLOCKCARDCAROUSELELEMENT'),
			'label' => array('.landing-block-node-img'),
		),
	),
	'nodes' => array(
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_IMAGE_CAROUSEL_6_COLS_FIX_3_2_NODES_LANDINGBLOCKNODEBGIMG'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'dimensions' => array('width' => 1920, 'height' => 350),
			'create2xByDefault' => false,
			'isWrapper' => true,
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_IMAGE_CAROUSEL_6_COLS_FIX_3_2_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 250, 'height' => 200),
			'create2xByDefault' => false,
		),
	),
	'style' => array(
		'block' => array(
			'type' => ['block-default-background', 'animation'],
		),
		'nodes' => array(
			'.landing-block-card-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_9_IMAGE_CAROUSEL_6_COLS_FIX_3_2_CARDS_LANDINGBLOCKCARDCAROUSELELEMENT'),
				'type' => ['row-align-column', 'align-items-column'],
			),
			'.landing-block-slider' => [
				'additional' => [
					'name' => Loc::getMessage('LANDING_BLOCK_9_IMAGE_CAROUSEL_6_COLS_FIX_3_2_NODES_SLIDER'),
					'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show-extended', 'arrows', 'dots'],
				]
			],
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);