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
	'style' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => array('background-color', 'background-gradient'),
		),
		'.landing-block-node-card-text-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARD_TEXT_CONTAINER'),
			'type' => 'animation',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.3.FOUR_COLUMNS_TEXT_WITH_IMG_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'typo',
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
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);