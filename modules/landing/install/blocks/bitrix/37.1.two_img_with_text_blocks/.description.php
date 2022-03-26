<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_37.1.TWO_IMG_WITH_TEXT_BLOCKS_NAME'),
		'section' => array('tiles'),
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.1.TWO_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.1.TWO_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.1.TWO_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODE_IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 540),
			'create2xByDefault' => false,
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.1.TWO_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-node-block' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.1.TWO_IMG_WITH_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODE_BLOCK'),
			'type' => array('box', 'paddings'),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.1.TWO_IMG_WITH_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => ['typo', 'animation', 'heading'],
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.1.TWO_IMG_WITH_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => array('typo', 'animation'),
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.1.TWO_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => array('border-color', 'button', 'animation'),
		),
		'.landing-block-node-title-add' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.1.TWO_IMG_WITH_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODETEXTADD'),
			'type' => 'color',
		),
		'.landing-block-node-button-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.1.TWO_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'text-align',
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_37.1.TWO_IMG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODE_IMG'),
			'type' => array('background-size', 'bg'),
		),
	),
);