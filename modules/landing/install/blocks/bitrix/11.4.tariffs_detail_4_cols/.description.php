<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
//			'name' => Loc::getMessage('LANDING_BLOCK_11.4.TARIFFS_DETAIL_3_COLS_NAME'),
			'section' => array('tariffs'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_11.4.TARIFFS_DETAIL_3_COLS_CARDS_LANDINGBLOCKNODECARD'),
					'label' => array('.landing-block-node-card-icon', '.landing-block-node-card-title')
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_11.4.TARIFFS_DETAIL_3_COLS_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'text',
				),
			'.landing-block-node-card-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_11.4.TARIFFS_DETAIL_3_COLS_NODES_LANDINGBLOCKNODECARDTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-card-icon' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_11.4.TARIFFS_DETAIL_3_COLS_NODES_LANDINGBLOCKNODECARDICON'),
					'type' => 'icon',
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_11.4.TARIFFS_DETAIL_3_COLS_NODES_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'text',
				),
			'.landing-block-node-button' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_11.4.TARIFFS_DETAIL_3_COLS_NODES_LANDINGBLOCKNODEBUTTON'),
					'type' => 'link',
				),
		),
	'style' =>
		array(
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_11.4.TARIFFS_DETAIL_3_COLS_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-card-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_11.4.TARIFFS_DETAIL_3_COLS_NODES_LANDINGBLOCKNODECARDTITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-card-icon' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_11.4.TARIFFS_DETAIL_3_COLS_NODES_LANDINGBLOCKNODECARDICON'),
					'type' => 'color',
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_11.4.TARIFFS_DETAIL_3_COLS_NODES_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'typo',
				),
			'.landing-block-node-button' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_11.4.TARIFFS_DETAIL_3_COLS_NODES_LANDINGBLOCKNODEBUTTON'),
					'type' => 'button',
				),
		),
);