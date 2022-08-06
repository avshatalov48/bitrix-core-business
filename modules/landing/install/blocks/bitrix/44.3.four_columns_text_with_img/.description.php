<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NAME'),
		'section' => array('tiles', 'news'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-img', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARDIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 480),
			'create2xByDefault' => false,
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'link',
		),
	),
	'style' => [
		'block' => [
			'type' => ['block-default-background-overlay'],
			'additional' => [
				'name' => Loc::getMessage('LANDING_BLOCK_44_3_FOUR_COLUMNS_TEXT_WITH_IMG_NODES_SLIDER'),
				'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show-extended', 'arrows', 'dots'],
			]
		],
		'nodes' => [
			'.landing-block-node-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARD_TITLE'),
				'type' => ['background-color', 'background-color-hover'],
			),
			'.landing-block-node-card-text-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARD_TEXT_CONTAINER'),
				'type' => 'animation',
			),
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARD_TITLE'),
				'type' => ['typo', 'heading'],
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARDTEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-card-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARDBUTTON'),
				'type' => 'button',
			),
			'.landing-block-node-card-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARDBUTTON'),
				'type' => 'text-align',
			),
		],
	],
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);