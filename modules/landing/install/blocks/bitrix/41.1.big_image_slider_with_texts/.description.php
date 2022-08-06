<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_NAME'),
		'section' => array('cover'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_CARDS_LANDINGBLOCKNODECARD'),
			'label' => ['.landing-block-node-card-bgimg', '.landing-block-node-card-title'],
		),
	),
	'nodes' => array(
		'.landing-block-node-card-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_NODES_LANDINGBLOCKNODECARD_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-photo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_NODES_LANDINGBLOCKNODECARDPHOTO'),
			'type' => 'img',
			'dimensions' => array('width' => 500, 'height' => 500),
		),
		'.landing-block-node-card-name' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_NODES_LANDINGBLOCKNODECARDNAME'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-price' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_NODES_LANDINGBLOCKNODECARDPRICE'),
			'type' => 'text',
		),
		'.landing-block-node-card-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_NODES_LANDINGBLOCKNODEBGIMG'),
			'type' => 'img',
			'allowInlineEdit' => false,
			'useInDesigner' => false,
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'create2xByDefault' => false,
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-wo-background'),
			'additional' => [
				'name' => Loc::getMessage('LANDING_BLOCK_41_BIG_IMAGE_SLIDER_WITH_TEXTS_STYLE_SLIDER'),
				'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show-extended', 'dots'],
			]
		),
		'nodes' => array(
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_STYLE_LANDINGBLOCKNODECARDTITLE'),
				'type' => ['typo', 'heading'],
			),
			'.landing-block-node-card-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_STYLE_LANDINGBLOCKNODECARDSUBTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-card-name' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_STYLE_LANDINGBLOCKNODECARDNAME'),
				'type' => 'typo',
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_STYLE_LANDINGBLOCKNODECARDTEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-card-price' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_STYLE_LANDINGBLOCKNODECARDPRICE'),
				'type' => 'typo',
			),
			'.landing-block-node-card-bgimg' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_NODES_LANDINGBLOCKNODEBGIMG'),
				'type' => ['background-overlay', 'padding-top', 'padding-bottom', 'height-vh']
			),
			'.landing-block-node-card-icon-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_NODES_LANDINGBLOCKNODECARD_ICON'),
				'type' => 'color',
			),
			'.landing-block-node-card-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.BIG_IMAGE_SLIDER_WITH_TEXTS_NODES_LANDINGBLOCKNODE_CONTAINER'),
				'type' => ['animation', 'align-self']
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);