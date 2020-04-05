<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NAME'),
			'section' => array('tariffs'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_CARDS_LANDINGBLOCKNODECARD'),
					'label' => array('.landing-block-node-card-title', '.landing-block-node-card-subtitle'),
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-card-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-card-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-card-img' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDIMG'),
					'type' => 'img',
					'dimensions' => array('width' => 740, 'height' => 380),
				),
			'.landing-block-node-card-price-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDPRICESUBTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-card-price' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDPRICE'),
					'type' => 'text',
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'text',
				),
			'.landing-block-node-card-button' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDBUTTON'),
					'type' => 'link',
				),
		),
	'style' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_CARDS_LANDINGBLOCKNODECARD'),
					'type' => array('columns','animation'),
				),
			'.landing-block-node-card-container-top' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_CARDS_LANDINGBLOCKNODECARD_CONTAINER_TOP'),
					'type' => array('box'),
				),
			'.landing-block-node-card-container-bottom' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_CARDS_LANDINGBLOCKNODECARD_CONTAINER_BOTTOM'),
					'type' => array('box'),
				),
			'.landing-block-node-card-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDTITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-card-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-card-price-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDPRICESUBTITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-card-price' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDPRICE'),
					'type' => 'typo',
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'typo',
				),
			'.landing-block-node-card-button' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDBUTTON'),
					'type' => 'button',
				),
			'.landing-block-node-card-button-container' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.7.THREE_COLUMNS_WITH_IMG_AND_PRICE_NODES_LANDINGBLOCKNODECARDBUTTON'),
					'type' => 'text-align',
				),
		),
);