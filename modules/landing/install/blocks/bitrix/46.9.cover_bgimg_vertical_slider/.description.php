<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_46.9_NAME'),
		'section' => array('cover'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.9_NODES_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-img','.landing-block-node-card-subtitle'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.9_NODES_LANDINGBLOCKNODECARD_IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'allowInlineEdit' => false,
		),
		'.landing-block-node-card-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.9_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.9_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.9_NODES_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-wo-background'),
		),
		'nodes' => array(
			'.landing-block-node-text-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.9_NODES_LANDINGBLOCKNODECARD_TEXT_CONTAINER'),
				'type' => 'animation',
			),
			'.landing-block-node-card-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.9_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.9_NODES_LANDINGBLOCKNODECARDTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-card-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.9_NODES_LANDINGBLOCKNODECARDBUTTON'),
				'type' => 'button',
			),
			'.landing-block-node-card-img' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.9_NODES_LANDINGBLOCKNODECARD_IMG'),
				'type' => array('background-overlay', 'height-vh'),
			),
			'.landing-block-node-card-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_46.9_NODES_LANDINGBLOCKNODECARDBUTTON'),
				'type' => 'text-align',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);