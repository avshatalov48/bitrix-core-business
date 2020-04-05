<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_9_TWO_COLS_FIX_IMG_AND_LINKS_NAME'),
		'section' => array('tiles'),
	),
	'cards' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_TWO_COLS_FIX_IMG_AND_LINKS_NODES_LANDINGBLOCKNODE_CARD'),
			'label' => array('.landing-block-node-img', '.landing-block-node-link'),
		),
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_TWO_COLS_FIX_IMG_AND_LINKS_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 500, 'height' => 335),
		),
		'.landing-block-node-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_TWO_COLS_FIX_IMG_AND_LINKS_NODES_LANDINGBLOCKNODELINK'),
			'type' => 'link',
		),
		'.landing-block-node-link-more' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_TWO_COLS_FIX_IMG_AND_LINKS_NODES_LANDINGBLOCKNODELINKMORE'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_TWO_COLS_FIX_IMG_AND_LINKS_NODES_LANDINGBLOCKNODE_CARD'),
			'type' => array('columns', 'animation'),
		),
		'.landing-block-node-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_TWO_COLS_FIX_IMG_AND_LINKS_NODES_LANDINGBLOCKNODELINK'),
			'type' => 'typo',
		),
		'.landing-block-node-link-more' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_TWO_COLS_FIX_IMG_AND_LINKS_NODES_LANDINGBLOCKNODELINKMORE'),
			'type' => 'typo',
		),
	),

);