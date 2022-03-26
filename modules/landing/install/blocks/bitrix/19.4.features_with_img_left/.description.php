<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NAME'),
		'section' => array('about', 'text_image'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 445),
			'create2xByDefault' => false,
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODE_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => ['typo', 'heading'],
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODE_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_CARDS_LANDINGBLOCKNODECARD'),
			'type' => 'box',
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'animation',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-img-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.4.FEATURES_WITH_IMG_LEFT_NODES_LANDINGBLOCKNODEIMG'),
			'type' => array('align-self'),
		),
	),
);