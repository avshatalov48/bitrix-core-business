<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_NAME'),
		'section' => array('steps'),
	),
	'cards' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_NODES_LANDINGBLOCK_CARD'),
			'label' => array('.landing-block-node-element-icon', '.landing-block-node-element-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-element-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_NODES_LANDINGBLOCKNODE_ELEMENT_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-element-icon-hover' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_NODES_LANDINGBLOCKNODE_ELEMENT_ICON_HOVER'),
			'type' => 'img',
			'dimensions' => array('width' => 100, 'height' => 100),
		),
		'.landing-block-node-element-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_NODES_LANDINGBLOCKNODE_ELEMENT_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-element-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_NODES_LANDINGBLOCKNODE_ELEMENT_TEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_STYLE_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-element-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_NODES_LANDINGBLOCKNODE_ELEMENT_TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-element-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_NODES_LANDINGBLOCKNODE_ELEMENT_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_NODES_LANDINGBLOCK_CARD'),
			'type' => array('columns', 'animation'),
		),
		'.landing-block-card-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_STYLE_LANDINGBLOCKNODEELEMENTCONTAINER'),
			'type' => array('bg', 'box-shadow'),
		),
		'.landing-block-node-element-icon-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_STYLE_LANDINGBLOCKNODEELEMENTICON'),
			'type' => array('background-color', 'color', 'box-shadow'),
		),
		'.landing-block-node-header' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_WITH_TITLE_STYLE_LANDINGBLOCKNODEHEADER'),
			'type' => 'border-color',
		),
		'.landing-block-inner' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_TWO_COL_FIX_TEXT_WITH_ICON_NODES_LANDINGBLOCK_INNER'),
			'type' => 'row-align',
		),
	),
);