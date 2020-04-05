<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_44.6.TWO_COLUMNS_WITH_PEOPLES_NAME'),
			'section' => array('team'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.6.TWO_COLUMNS_WITH_PEOPLES_CARDS_LANDINGBLOCKNODECARD'),
					'label' => array('.landing-block-node-card-photo', '.landing-block-node-card-name')
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-card-photo' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.6.TWO_COLUMNS_WITH_PEOPLES_NODES_LANDINGBLOCKNODECARDPHOTO'),
					'type' => 'img',
					'dimensions' => array('width' => 300, 'height' => 300),
				),
			'.landing-block-node-card-name' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.6.TWO_COLUMNS_WITH_PEOPLES_NODES_LANDINGBLOCKNODECARDNAME'),
					'type' => 'text',
				),
			'.landing-block-node-card-post' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.6.TWO_COLUMNS_WITH_PEOPLES_NODES_LANDINGBLOCKNODECARDPOST'),
					'type' => 'text',
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.6.TWO_COLUMNS_WITH_PEOPLES_NODES_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.6.TWO_COLUMNS_WITH_PEOPLES_CARDS_LANDINGBLOCKNODECARD'),
					'type' => array('columns','box','animation'),
				),
			'.landing-block-node-card-name' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.6.TWO_COLUMNS_WITH_PEOPLES_NODES_LANDINGBLOCKNODECARDNAME'),
					'type' => 'typo',
				),
			'.landing-block-node-card-post' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.6.TWO_COLUMNS_WITH_PEOPLES_NODES_LANDINGBLOCKNODECARDPOST'),
					'type' => 'typo',
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_44.6.TWO_COLUMNS_WITH_PEOPLES_NODES_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'typo',
				),
		),
);