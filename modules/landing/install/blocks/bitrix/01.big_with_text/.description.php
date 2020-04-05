<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_NAME'),
		'section' => array('cover'),
	),
	'cards' => array(
		'.landing-block-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_CARDS_LANDINGBLOCKNODEIMG'),
			'label' => array('.landing-block-card-img')
			//					'allowInlineEdit' => false,
		),
	),
	'nodes' => array(
		'.landing-block-node-small-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_NODES_LANDINGBLOCKNODESMALLTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'link',
		),
		'.landing-block-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_CARDS_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'allowInlineEdit' => false,
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('display'),
		),
		'nodes' => array(
			'.landing-block-node-text-container' => array(
				'title' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_NODES_LANDINGBLOCKNODE_TEXT_CONTAINER_NEW'),
				'type' => array('background-color', 'background-gradient', 'animation'),
			),
			'.landing-block-node-small-title' => array(
				'title' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_NODES_LANDINGBLOCKNODESMALLTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-title' => array(
				'title' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_NODES_LANDINGBLOCKNODETITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-text' => array(
				'title' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_NODES_LANDINGBLOCKNODETEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-button' => array(
				'title' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_NODES_LANDINGBLOCKNODEBUTTON'),
				'type' => 'button',
			),
			'.landing-block-card-img' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1BIG_WITH_TEXT_CARDS_LANDINGBLOCKNODEIMG'),
				'type' => 'height-vh',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);