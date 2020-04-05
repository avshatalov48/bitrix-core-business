<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.4.COVER_WITH_SLIDER_BGIMG_RIGHT_BUTTONS_NAME'),
			'section' => array('cover'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.4.COVER_WITH_SLIDER_BGIMG_RIGHT_BUTTONS_CARDS_LANDINGBLOCKNODECARD'),
					'label' => array('.landing-block-node-card-title', '.landing-block-node-card-subtitle'),
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-card-bgimg' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.4.COVER_WITH_SLIDER_BGIMG_RIGHT_BUTTONS_NODES_LANDINGBLOCKNODECARDBGIMG'),
					'type' => 'img',
					'allowInlineEdit' => false,
					'dimensions' => array('width' => 1920, 'height' => 1080),
				),
			'.landing-block-node-card-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.4.COVER_WITH_SLIDER_BGIMG_RIGHT_BUTTONS_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-card-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.4.COVER_WITH_SLIDER_BGIMG_RIGHT_BUTTONS_NODES_LANDINGBLOCKNODECARDTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.4.COVER_WITH_SLIDER_BGIMG_RIGHT_BUTTONS_NODES_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'block' => array(
				'type' => array('block-default-wo-background'),
			),
			'nodes' => array(
				'.landing-block-node-card-text-container' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.4.COVER_WITH_SLIDER_BGIMG_RIGHT_BUTTONS_NODES_LANDINGBLOCKNODECARDS_TEXT_CONTAINER'),
						'type' => array('border-color', 'animation'),
					),
				'.landing-block-node-card-subtitle' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.4.COVER_WITH_SLIDER_BGIMG_RIGHT_BUTTONS_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
						'type' => 'typo',
					),
				'.landing-block-node-card-title' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.4.COVER_WITH_SLIDER_BGIMG_RIGHT_BUTTONS_NODES_LANDINGBLOCKNODECARDTITLE'),
						'type' => 'typo',
					),
				'.landing-block-node-card-text' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.4.COVER_WITH_SLIDER_BGIMG_RIGHT_BUTTONS_NODES_LANDINGBLOCKNODECARDTEXT'),
						'type' => 'typo',
					),
				'.landing-block-node-card-bgimg' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.4.COVER_WITH_SLIDER_BGIMG_RIGHT_BUTTONS_NODES_LANDINGBLOCKNODECARDBGIMG'),
						'type' => 'background-overlay',
					),
			),
		),
	'assets' => array(
	    'ext' => array('landing_carousel'),
	),
);