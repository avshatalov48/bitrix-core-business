<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_42.1.REST_MENU_NAME'),
			'section' => array('tariffs'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_42.1.REST_MENU_CARDS_LANDINGBLOCKNODECARD'),
					'label' => array('.landing-block-node-card-photo', '.landing-block-node-card-title')
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-card-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_42.1.REST_MENU_NODES_LANDINGBLOCKNODECARDTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_42.1.REST_MENU_NODES_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'text',
				),
			'.landing-block-node-card-price' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_42.1.REST_MENU_NODES_LANDINGBLOCKNODECARDPRICE'),
					'type' => 'text',
				),
			'.landing-block-node-card-photo' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_42.1.REST_MENU_NODES_LANDINGBLOCKNODECARDPHOTO'),
					'type' => 'img',
					'dimensions' => array('width' => 500, 'height' => 500),
				),
		),
	'style' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_42.1.REST_MENU_CARDS_LANDINGBLOCKNODECARD'),
					'type' => array('columns', 'animation'),
				),
			'.landing-block-node-card-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_42.1.REST_MENU_STYLE_LANDINGBLOCKNODECARDTITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_42.1.REST_MENU_STYLE_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'typo',
				),
			'.landing-block-node-card-price' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_42.1.REST_MENU_STYLE_LANDINGBLOCKNODECARDPRICE'),
					'type' => array('background-color', 'typo'),
				),
		),
);