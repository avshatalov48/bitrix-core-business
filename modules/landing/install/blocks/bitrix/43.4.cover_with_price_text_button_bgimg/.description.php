<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NAME'),
		'section' => array('cover'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-bgimg', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-price' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_LANDINGBLOCKNODECARDPRICE'),
			'type' => 'text',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'link',
		),
		'.landing-block-node-card-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_LANDINGBLOCKNODECARDBGIMG'),
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
				'name' => Loc::getMessage('LANDING_BLOCK_43_4_COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_SLIDER'),
				'attrsType' => ['autoplay', 'autoplay-speed', 'pause-hover', 'dots'],
			]
		),
		'nodes' => array(
			'.landing-block-node-card-price' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_LANDINGBLOCKNODECARDPRICE'),
				'type' => array('typo', 'box', 'paddings'),
			),
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_LANDINGBLOCKNODECARDTITLE'),
				'type' => ['typo', 'heading'],
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_LANDINGBLOCKNODECARDTEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-card-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_LANDINGBLOCKNODECARDBUTTON'),
				'type' => 'button',
			),
			'.landing-block-node-card-bgimg' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_LANDINGBLOCKNODECARDBGIMG'),
				'type' => array('align-items', 'row-align', 'background-overlay', 'height-vh', 'paddings'),
			),
			'.landing-block-node-card-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_LANDINGBLOCKNODECARD_CONTAINER'),
				'type' => ['align-self', 'text-align', 'padding-top', 'padding-bottom', 'animation'],
			),
			'.landing-block-node-card-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.4.COVER_WITH_PRICE_TEXT_BUTTON_BGIMG_NODES_LANDINGBLOCKNODECARDBUTTONCONTAINER'),
				'type' => 'text-align',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);