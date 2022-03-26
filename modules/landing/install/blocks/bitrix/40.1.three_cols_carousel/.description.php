<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_NAME'),
		'section' => array('cover'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-title', '.landing-block-node-card-subtitle'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_NODES_LANDINGBLOCKNODECARDICON'),
			'type' => 'icon',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_NODES_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'link',
		),
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_NODES_LANDINGBLOCKNODECARDIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 610, 'height' => 1000),
			'useInDesigner' => false,
			'create2xByDefault' => false,
		),
	),
	'style' => array(
		'.landing-block-node-card-icon-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_NODES_LANDINGBLOCKNODECARDICON'),
			'type' => 'color',
		),
		'.landing-block-node-card-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDSUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDTITLE'),
			'type' => ['typo', 'heading'],
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'button',
		),
		'.landing-block-node-card-img-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDIMGCONTAINER'),
			'type' => 'background-overlay',
		),
		'.landing-block-node-card-button-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'text-align',
		),
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.1.THREE_COLS_CAROUSEL_CARDS_LANDINGBLOCKNODECARD'),
			'type' => 'align-self',
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);