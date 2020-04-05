<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_40.2.TWO_COLS_CAROUSEL_NAME_NEW'),
		'section' => array('image'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.2.TWO_COLS_CAROUSEL_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-img', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-date' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.2.TWO_COLS_CAROUSEL_NODES_LANDINGBLOCKNODECARDDATE'),
			'type' => 'text',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.2.TWO_COLS_CAROUSEL_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.2.TWO_COLS_CAROUSEL_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.2.TWO_COLS_CAROUSEL_NODES_LANDINGBLOCKNODECARDIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1332, 'height' => 806),
		),
	),
	'style' => array(
		'.landing-block-node-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.2.TWO_COLS_CAROUSEL_STYLE_LANDINGBLOCKNODECARD_CONTAINER'),
			'type' => 'animation',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.2.TWO_COLS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.2.TWO_COLS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-date' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_40.2.TWO_COLS_CAROUSEL_STYLE_LANDINGBLOCKNODECARDDATE'),
			'type' => 'typo',
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);