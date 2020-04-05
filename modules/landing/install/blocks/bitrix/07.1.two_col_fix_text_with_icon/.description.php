<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_NAME'),
		'section' => array('steps'),
	),
	'cards' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_NODES_LANDINGBLOCK_CARD'),
			'label' => array('.landing-block-node-icon', '.landing-block-node-title')
		),
	),
	'nodes' => array(
		'.landing-block-node-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_NODES_LANDINGBLOCKNODEICON'),
			'type' => 'icon',
		),
		'.landing-block-node-icon-hover' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_NODES_LANDINGBLOCKNODEICONHOVER'),
			'type' => 'img',
			'dimensions' => array('width' => 100, 'height' => 100),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_NODES_LANDINGBLOCK_CARD'),
			'type' => array('columns', 'animation'),
		),
		'.landing-block-card-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_STYLE_LANDINGBLOCKNODECONTAINER'),
			'type' => array('background-color', 'background-gradient', 'box-shadow'),
		),
		'.landing-block-node-element-icon-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_STYLE_LANDINGBLOCKNODEELEMENTICON'),
			'type' => array('background-color', 'background-gradient', 'box-shadow'),
		),
	),
);