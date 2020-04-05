<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NAME'),
		'section' => array('columns', 'text'),
	),
	'cards' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODE_CARD'),
			'label' => array('.landing-block-node-title', '.landing-block-node-subtitle'),
		),
	),
	'nodes' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODE_CARD'),
			'type' => array('columns', 'animation'),
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_STYLE_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-card-header' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_STYLE_LANDINGBLOCKCARHEADER'),
			'type' => 'border-color',
		),
		'.landing-block-inner' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODE_INNER'),
			'type' => 'row-align',
		),
	),
);