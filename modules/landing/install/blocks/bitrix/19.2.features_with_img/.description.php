<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NAME'),
		'section' => array('text_image', 'about'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODE_CARD'),
			'label' => array('.landing-block-node-card-icon', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 445),
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODECARD_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-icon-border' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODECARD_ICON'),
			'type' => array('border-color', 'color'),
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'animation',
		),
		'.landing-block-node-text-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_NODES_LANDINGBLOCKNODETEXTCONTAINER'),
			'type' => 'align-items',
		),
	),
);