<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG_NAME'),
		'section' => array('cover'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-title'),
			'presets' => include __DIR__ . '/presets.php',
		),
	),
	'nodes' => array(
		'.landing-block-node-card-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG_NODES_LANDINGBLOCKNODECARDBGIMG'),
			'type' => 'img',
			'allowInlineEdit' => false,
			'dimensions' => array('width' => 1920, 'height' => 1080),
		),
		'.landing-block-node-card-videobg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG_NODES_LANDINGBLOCKNODECARD--BGVIDEO'),
			'type' => 'embed',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG_NODES_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-wo-background'),
		),
		'nodes' => array(
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG_NODES_LANDINGBLOCKNODECARDTITLE'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-card-text-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG_NODES_LANDINGBLOCKNODECARD_TEXT_CONTAINER'),
				'type' => array('animation'),
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG_NODES_LANDINGBLOCKNODECARDTEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-card-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG_NODES_LANDINGBLOCKNODECARDBUTTON'),
				'type' => 'button',
			),
			'.landing-block-node-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.6.COVER_WITH_SLIDER_BGIMG_CARDS_LANDINGBLOCKNODECARD'),
				'type' => array('align-items', 'height-vh','background-overlay'),
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel', 'landing_inline_video'),
	),
);