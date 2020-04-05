<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NAME'),
		'description' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_DESCRIPTION'),
		'section' => array('columns', 'text'),
	),
	'cards' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NODES_LANDINGBLOCK_CARD'),
			'label' => array(
				'.landing-block-node-subtitle',
				'.landing-block-node-title',
			),
		),
	),
	'nodes' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		
		//			to updater fixes
		//			'.landing-block-inner-container' => array(),
	),
	'style' => array(
		'.landing-block-inner-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NODES_LANDINGBLOCK_COLS'),
			'type' => array('row-align'),
		),
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_NODES_LANDINGBLOCK_CARD'),
			'type' => array('columns', 'animation'),
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_STYLE_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-header' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_THREE_COLS_4_STYLE_LANDINGBLOCKNODECARDHEADER'),
			'type' => 'border-color',
		),
	),
);