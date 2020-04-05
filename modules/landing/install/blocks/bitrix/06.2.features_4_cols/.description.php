<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_NAME'),
			'section' => array('columns'),
		),
	'cards' =>
		array(
			'.landing-block-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_3_COLS_NODES_LANDINGBLOCK_CARD'),
				'label' => array('.landing-block-node-element-icon', '.landing-block-node-element-title')
			),
		),
	'nodes' =>
		array(
			'.landing-block-node-element-icon' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_3_COLS_NODES_LANDINGBLOCKNODEELEMENTICON'),
					'type' => 'icon',
				),
			'.landing-block-node-element-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_3_COLS_NODES_LANDINGBLOCKNODEELEMENTTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-element-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_3_COLS_NODES_LANDINGBLOCKNODEELEMENTTEXT'),
					'type' => 'text',
				),
			'.landing-block-node-element-list' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_3_COLS_STYLE_LANDINGBLOCKNODEELEMENTLISTITEM'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'.landing-block-node-element' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_3_COLS_STYLE_LANDINGBLOCKNODEELEMENT'),
					'type' => array('columns', 'box', 'animation'),
				),
			'.landing-block-node-element-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_3_COLS_STYLE_LANDINGBLOCKNODEELEMENTTITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-element-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_3_COLS_STYLE_LANDINGBLOCKNODEELEMENTTEXT'),
					'type' => 'typo',
				),
			'.landing-block-node-element-list-item' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_3_COLS_STYLE_LANDINGBLOCKNODEELEMENTLISTITEM'),
					'type' => 'typo',
				),
			'.landing-block-node-element-icon' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_3_COLS_NODES_LANDINGBLOCKNODEELEMENTICON'),
					'type' => 'color',
				),
			'.landing-block-node-separator' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_3_COLS_STYLE_LANDINGBLOCKNODESEPARATOR'),
					'type' => 'border-color',
				),
		),
);