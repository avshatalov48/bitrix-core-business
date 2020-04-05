<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_BUTTON_NAME'),
			'section' => array('tiles'),
		),
	'cards' =>
		array(),
	'nodes' =>
		array(
			'.landing-block-node-img' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_BUTTON_NODES_LANDINGBLOCKIMG'),
					'type' => 'img',
					'dimensions' => array('width' => 874, 'height' => 600),
				),
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_BUTTON_NODES_LANDINGBLOCKTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_BUTTON_NODES_LANDINGBLOCKTEXT'),
					'type' => 'text',
				),
			'.landing-block-node-button' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_BUTTON_NODES_LANDINGBLOCKBUTTON'),
					'type' => 'link',
				),
		),
	'style' =>
		array(
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_BUTTON_STYLE_LANDINGBLOCKTITLE'),
					'type' => array('typo','animation'),
				),
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_BUTTON_STYLE_LANDINGBLOCKTEXT'),
					'type' => array('typo','animation'),
				),
			'.landing-block-node-button' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_BUTTON_STYLE_LANDINGBLOCKBUTTON'),
					'type' => array('button','animation'),
				),
		),
);