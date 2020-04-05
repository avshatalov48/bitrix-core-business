<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NAME'),
		'section' => array('columns', 'text_image'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-img', '.landing-block-node-card-title')
		),
	),
	'nodes' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDIMG2'),
			'type' => 'img',
			'dimensions' => array('width' => 570),
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTITLE2'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTEXT2'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => array('typo', 'animation'),
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => array('typo', 'animation'),
		),
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_CARDS_LANDINGBLOCKNODECARD'),
			'type' => array('columns', 'background-color', 'background-gradient', 'animation'),
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTITLE2'),
			'type' => 'typo',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTEXT2'),
			'type' => 'typo',
		),
		'.landing-block-inner' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_CARDS_LANDINGBLOCK_INNER'),
			'type' => 'row-align',
		),
	),
);