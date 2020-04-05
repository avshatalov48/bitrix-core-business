<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NAME'),
		'description' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_DESCRIPTION'),
		'section' => array('text_image', 'about'),
	),
	'cards' => array(
		'.landing-block-card-text-block' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_CARDS_LANDINGBLOCKCARDTEXTBLOCK'),
			'label' => array('.landing-block-node-text-block-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('height' => 1080),
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-text-block-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETEXTBLOCKTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text-block-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETEXTBLOCKTEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-texts' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODETEXTS'),
			'type' => 'box',
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-card-text-block article' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_CARDS_LANDINGBLOCKCARDTEXTBLOCK'),
			'type' => array('box', 'paddings', 'border-color', 'animation'),
		),
		'.landing-block-node-text-block-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETEXTBLOCKTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text-block-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETEXTBLOCKTEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-header' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODEHEADER'),
			'type' => array('text-align', 'border-color'),
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_TWO_COLS_BIG_IMG_TEXT_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'background-size',
		),
	),
);