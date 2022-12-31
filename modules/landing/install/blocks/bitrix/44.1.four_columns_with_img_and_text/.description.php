<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NAME'),
		'section' => array('columns', 'text_image'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-img', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text-unhover' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTEXT_UNHOVER'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDIMG'),
			'type' => 'img',
			'dimensions' => ['maxWidth' => 496],
			'useInDesigner' => false,
		),
	),
	'style' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_CARDS_LANDINGBLOCKNODECARD'),
			'type' => ['columns', 'background-color', 'background-overlay', 'color', 'color-hover'],
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => ['typo', 'heading'],
		),
		'.landing-block-node-card-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'typo',
		),
		'.landing-block-inner' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_CARDS_LANDINGBLOCKNODECARD'),
			'type' => 'row-align',
		),
		//for old version block
		'.landing-block-node-card-inner' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.1.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDINNER'),
			'type' => ['background-color', 'background-overlay'],
		),
	),
);