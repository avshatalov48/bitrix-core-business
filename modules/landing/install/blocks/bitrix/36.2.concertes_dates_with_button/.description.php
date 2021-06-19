<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NAME'),
		'section' => array('schedule'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-img', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-date-value' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDDATEVALUE'),
			'type' => 'text',
		),
		'.landing-block-node-card-date-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDDATETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 130),
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-price' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDPRICE'),
			'type' => 'text',
		),
		'.landing-block-node-card-price-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDPRICETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_CARDS_LANDINGBLOCKNODECARD'),
			'type' => array('box', 'animation'),
		),
		'.landing-block-node-card-date-value' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDDATEVALUE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-date-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDDATETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => ['typo', 'heading'],
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-price' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDPRICE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-price-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDPRICETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.2.CONCERTES_DATES_WITH_BUTTON_NODES_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'button',
		),
	),
);