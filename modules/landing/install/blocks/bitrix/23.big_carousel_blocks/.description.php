<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_8_BIG_CAROUSEL_BLOCKS_NAME'),
		'section' => array('feedback'),
		'type' => ['page', 'store'],
	),
	'cards' => array(
		'.landing-block-card-carousel-element' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_8_BIG_CAROUSEL_BLOCKS_CARDS_LANDINGBLOCKCARDCAROUSELELEMENT'),
			'label' => array('.landing-block-node-img', '.landing-block-node-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_8_BIG_CAROUSEL_BLOCKS_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 100),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_8_BIG_CAROUSEL_BLOCKS_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_8_BIG_CAROUSEL_BLOCKS_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_8_BIG_CAROUSEL_BLOCKS_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default', 'animation'),
		),
		'nodes' => array(
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_8_BIG_CAROUSEL_BLOCKS_STYLE_LANDINGBLOCKNODETITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_8_BIG_CAROUSEL_BLOCKS_STYLE_LANDINGBLOCKNODESUBTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_8_BIG_CAROUSEL_BLOCKS_STYLE_LANDINGBLOCKNODETEXT'),
				'type' => array('typo', 'background-color-before'),
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);