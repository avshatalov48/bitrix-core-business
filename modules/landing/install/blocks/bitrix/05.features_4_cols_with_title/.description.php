<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NAME'),
			'section' => array('columns'),
		),
	'cards' =>
		array(
			'.landing-block-card' =>
			array(
				'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDING_CARD_ELEMENT'),
				'label' => array('.landing-block-node-element-icon', '.landing-block-node-element-title')
			)
		),
	'nodes' =>
		array(
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'text',
				),
			'.landing-block-node-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDINGBLOCKNODESUBTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-element-icon' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDINGBLOCKNODEELEMENTICON'),
					'type' => 'icon',
				),
			'.landing-block-node-element-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDINGBLOCKNODEELEMENTTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-element-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDINGBLOCKNODEELEMENTTEXT'),
					'type' => 'text',
				),
			'.landing-block-node-element-list' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDINGBLOCKNODEELEMENTLISTITEM'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDINGBLOCKNODESUBTITLE'),
					'type' => 'typo',
				),
			'.landing-block-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDING_CARD_ELEMENT'),
				'type' => array('columns', 'box','animation'),
			),
			'.landing-block-node-element-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDINGBLOCKNODEELEMENTTITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-element-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDINGBLOCKNODEELEMENTTEXT'),
					'type' => 'typo',
				),
			'.landing-block-node-element-list-item' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDINGBLOCKNODEELEMENTLISTITEM'),
					'type' => 'typo',
				),
			'.landing-block-node-element-icon' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_NODES_LANDINGBLOCKNODEELEMENTICON'),
					'type' => 'color',
				),
			'.landing-block-node-header' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_STYLE_LANDINGBLOCKNODEHEADER'),
					'type' => array('border-color','animation'),
				),
			'.landing-block-node-element-separator' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_4_FEATURES_4_COLS_WITH_TITLE_STYLE_LANDINGBLOCKNODESEPARATOR'),
					'type' => 'border-color',
				),
		),
);