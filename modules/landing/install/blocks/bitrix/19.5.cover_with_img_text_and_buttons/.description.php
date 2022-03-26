<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_19.5.COVER_WITH_IMG_TEXT_AND_BUTTONS_NAME'),
		'section' => array('cover'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.5.COVER_WITH_IMG_TEXT_AND_BUTTONS_NODES_LANDINGBLOCKNODE_CARD'),
			'label' => array('.landing-block-node-card-button-img'),
			'presets' => include __DIR__ . '/presets.php',
		),
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.5.COVER_WITH_IMG_TEXT_AND_BUTTONS_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.5.COVER_WITH_IMG_TEXT_AND_BUTTONS_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.5.COVER_WITH_IMG_TEXT_AND_BUTTONS_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 540),
			'create2xByDefault' => false,
		),
		'.landing-block-node-card-button-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.5.COVER_WITH_IMG_TEXT_AND_BUTTONS_NODES_LANDINGBLOCKNODE_BUTTON_IMG'),
			'type' => 'img',
			'group' => 'button',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.5.COVER_WITH_IMG_TEXT_AND_BUTTONS_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'link',
			'group' => 'button',
		),
	),
	'style' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.5.COVER_WITH_IMG_TEXT_AND_BUTTONS_NODES_LANDINGBLOCKNODETITLE'),
			'type' => ['typo', 'heading'],
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.5.COVER_WITH_IMG_TEXT_AND_BUTTONS_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19.5.COVER_WITH_IMG_TEXT_AND_BUTTONS_NODES_LANDINGBLOCKNODEIMG'),
			'type' => array('animation'),
		),
	),
);