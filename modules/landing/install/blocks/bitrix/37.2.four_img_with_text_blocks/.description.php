<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_NAME'),
			'section' => array('columns'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_CARDS_LANDINGBLOCKNODECARD'),
					'label' => array('.landing-block-node-card-bgimg', '.landing-block-node-card-title')
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-card-icon' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDICON'),
					'type' => 'icon',
				),
			'.landing-block-node-card-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'text',
				),
			'.landing-block-node-card-link' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDLINK'),
					'type' => 'link',
				),
			'.landing-block-node-card-bgimg' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDBGIMG'),
					'type' => 'img',
					'dimensions' => array('width' => 1024, 'height' => 683),
				),
		),
	'style' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_CARDS_LANDINGBLOCKNODECARD'),
					'type' => array('columns','animation'),
				),
			'.landing-block-node-card-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODECARDTITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'typo',
				),
			'.landing-block-node-card-link' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDLINK'),
					'type' => 'typo',
				),
			'.landing-block-node-card-icon' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDICON'),
					'type' => 'color',
				),
			'.landing-block-node-card-link-container' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_37.2.FOUR_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDLINK'),
					'type' => 'text-align',
				),
		),
);