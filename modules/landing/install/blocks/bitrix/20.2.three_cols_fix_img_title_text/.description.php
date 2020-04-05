<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NAME'),
		'section' => array('text_image', 'columns'),
	),
	'cards' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NODES_LANDINGBLOCK_CARD'),
			'label' => array('.landing-block-node-img', '.landing-block-node-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 800, 'height' => 466),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_NODES_LANDINGBLOCK_CARD'),
			'type' => array('columns', 'background-color', 'background-gradient', 'animation'),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TITLE_TEXT_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
	),
);