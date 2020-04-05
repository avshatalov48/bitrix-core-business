<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_34.4_NAME'),
		'section' => array('columns', 'about'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_34.4_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-icon', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_34.4_NODES_LANDINGBLOCKNODECARDICON'),
			'type' => 'icon',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_34.4_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_34.4_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
	
	),
	'style' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_34.4_CARDS_LANDINGBLOCKNODECARD'),
			'type' => array('columns', 'animation'),
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_34.4_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_34.4_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-icon-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_34.4_NODES_LANDINGBLOCKNODECARDICON'),
			'type' => 'color',
		),
	),
);