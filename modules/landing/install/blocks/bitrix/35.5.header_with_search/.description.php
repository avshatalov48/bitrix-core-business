<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NAME_NEW'),
		'section' => array('menu'),
		'type' => 'store',
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCK_CARD'),
			'label' => array('.landing-block-node-card-icon', '.landing-block-node-card-title')
		),
	),
	'nodes' => array(
		'.landing-block-node-logo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCKNODELOGO'),
			'type' => 'img',
			'group' => 'logo',
			'dimensions' => array('width' => 180, 'height' => 60),
		),
		'.landing-block-node-menu-logo-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCKNODELINK'),
			'type' => 'link',
			'group' => 'logo',
		),
		'.landing-block-node-card-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.3.HEADER_NODES_LANDINGBLOCKNODE_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.3.HEADER_NODES_LANDINGBLOCK_CARD_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.3.HEADER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.3.HEADER_NODES_LANDINGBLOCKNODELINK'),
			'type' => 'link',
		),
		'bitrix:search.title' => array(
			'type' => 'component',
		),
	),
	'style' => array(),
	'assets' => array(
		'js' => array(
			'/bitrix/components/bitrix/search.title/script.js',
		),
	),
);