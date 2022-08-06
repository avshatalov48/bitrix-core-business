<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_NAME'),
		'section' => array('cover', 'video'),
		'type' => ['page', 'store', 'smn'],
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_NODES_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'link',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_NODES_LANDINGBLOCKNODECARDLINK'),
			'type' => 'link',
		),
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_NODES_LANDINGBLOCKNODEBGIMG'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'create2xByDefault' => false,
			'isWrapper' => true,
		),
	),
	'style' => array(
		'block' => array(
			'type' => ['block-default-background', 'animation'],
			'additional' => [
				'name' => Loc::getMessage('LANDING_BLOCK_48_SLIDER_WITH_VIDEO_ON_BGIMG_NODES_SLIDER'),
				'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show', 'dots'],
			]
		),
		'nodes' => array(
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_NODES_LANDINGBLOCKNODECARDTITLE'),
				'type' => ['typo', 'heading'],
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_NODES_LANDINGBLOCKNODECARDTEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-card-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_NODES_LANDINGBLOCKNODECARDLINK'),
				'type' => 'typo-link',
			),
			'.landing-block-node-card-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_NODES_LANDINGBLOCKNODECARDBUTTON'),
				'type' => array('button-color', 'border-color'),
			),
			'.landing-block-node-card-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_NODES_LANDINGBLOCKNODECARDBUTTON'),
				'type' => array('text-align'),
			),
			'.landing-block-node-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_48.SLIDER_WITH_VIDEO_ON_BGIMG_CARDS_LANDINGBLOCKNODECARD'),
				'type' => array('align-self'),
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);