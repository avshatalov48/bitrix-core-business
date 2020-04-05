<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_NAME'),
		'section' => array('cover'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-img', '.landing-block-node-card-title')
			//				'allowInlineEdit' => false,
		),
	),
	'nodes' => array(
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARD_IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'allowInlineEdit' => false,
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-wo-background'),
		),
		'nodes' => array(
			'.landing-block-node-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODECARD_CONTAINER'),
				'type' => ['animation', 'align-self']
			),
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODECARDTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODECARDTEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-card-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDBUTTON'),
				'type' => 'button',
			),
			'.landing-block-node-card-img' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARD_IMG'),
				'type' => ['background-overlay', 'height-vh', 'paddings', 'background-attachment'],
			),
			'.landing-block-node-card-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDBUTTON'),
				'type' => 'text-align',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);