<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NAME'),
		'section' => array('cover'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-icon', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODEBGIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920, 'height' => 1080),
		),
		'.landing-block-node-card-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODECARDICON'),
			'type' => 'icon',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-background-overlay'),
		),
		'nodes' => array(
			'.landing-block-node-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_CARDS_LANDINGBLOCKNODECARD'),
				'type' => array('columns', 'animation'),
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODETITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODETEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODECARDTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODECARDTEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-card-icon-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODECARDICON'),
				'type' => 'color',
			),
			'.landing-block-node-header' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODEHEADER'),
				'type' => 'border-color',
			),
			'.landing-block-node-bgimg' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_41.3.COVER_WITH_TEXT_COLUMNS_ON_BGIMG_NODES_LANDINGBLOCKNODEBGIMG'),
				'type' => 'background-attachment',
			),
		),
	),
);