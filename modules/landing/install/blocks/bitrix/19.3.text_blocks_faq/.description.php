<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NAME'),
		'section' => array('tiles', 'news'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NODES_LANDINGBLOCKNODECARD_LINK'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_CARDS_LANDINGBLOCKNODECARD'),
			'type' => 'animation',
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NODES_LANDINGBLOCKNODETITLE'),
			'type' => array('typo', 'animation'),
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NODES_LANDINGBLOCKNODETEXT'),
			'type' => array('typo', 'animation'),
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.3.TEXT_BLOCKS_FAQ_NODES_LANDINGBLOCKNODECARD_LINK'),
			'type' => 'typo-link',
		),
	),
);