<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_NAME'),
		'section' => array('tiles'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-title', '.landing-block-node-card-subtitle'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-photo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODECARDPHOTO'),
			'type' => 'img',
			'dimensions' => array('width' => 600),
			'create2xByDefault' => false,
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'link',
		),
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODEBGIMG'),
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
			'type' => ['block-default-background'],
		),
		'nodes' => array(
			'.landing-block-node-card-text-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_CARDS_LANDINGBLOCKNODECARD_TEXT_CONTAINER'),
				'type' => array('box', 'paddings'),
			),
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODECARDTITLE'),
				'type' => ['typo', 'animation', 'heading'],
			),
			'.landing-block-node-card-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODECARDTEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-card-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_NODES_LANDINGBLOCKNODECARDBUTTON'),
				'type' => array('button', 'animation'),
			),
			'.landing-block-node-card-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46_3_BUTTON_AREA'),
				'type' => array('text-align'),
			),
			'.landing-block-node-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.3.COVER_WITH_BLOCKS_SLIDER_CARDS_LANDINGBLOCKNODECARD'),
				'type' => array('align-self'),
			),
			'.landing-block-slider' => [
				'additional' => [
					'name' => Loc::getMessage('LANDING_BLOCK_46_3_COVER_WITH_BLOCKS_SLIDER_NODES_SLIDER'),
					'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show', 'arrows', 'dots'],
				]
			],
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);