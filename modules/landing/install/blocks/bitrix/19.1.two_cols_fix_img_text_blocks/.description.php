<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_NAME'),
		'section' => array('about', 'text_image'),
	),
	'cards' => array(
		'.landing-block-card-accordeon-element' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_CARDS_LANDINGBLOCKCARDACCORDEONELEMENT'),
			'label' => array('.landing-block-node-accordeon-element-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 445),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-accordeon-element-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_NODES_LANDINGBLOCKNODEACCORDEONELEMENTIMG'),
			'type' => 'icon',
		),
		'.landing-block-node-accordeon-element-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_NODES_LANDINGBLOCKNODEELEMENTTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-accordeon-element-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_NODES_LANDINGBLOCKNODEACCORDEONELEMENTTEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-element-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_STYLE_LANDINGBLOCKNODEELEMENTTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-accordeon-element-img-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_NODES_LANDINGBLOCKNODEACCORDEONELEMENTIMG'),
			'type' => 'color',
		),
		'.landing-block-card-accordeon-element' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_CARDS_LANDINGBLOCKCARDACCORDEONELEMENT'),
			'type' => ['bg', 'paddings', 'animation'],
		),
		'.landing-block-card-accordeon-element-title-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_NODES_LANDINGBLOCKNODEELEMENTTITLE'),
			'type' => 'border-color',
		),
		'.landing-block-node-accordeon-element-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_NODES_LANDINGBLOCKNODEELEMENTTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-accordeon-element-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_STYLE_LANDINGBLOCKNODEACCORDEONELEMENTTEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-img-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_TWO_COLS_FIX_IMG_TEXT_ACCORDEON_NODES_LANDINGBLOCKNODEIMG'),
			'type' => array('text-align', 'margin-bottom')
		),
	),
);