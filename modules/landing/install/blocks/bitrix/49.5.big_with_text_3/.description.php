<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_49_5--NAME'),
		'section' => array('cover','video'),
		'version' => '18.5.0',
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-video' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_5--LANDINGBLOCKNODE_VIDEO'),
			'type' => 'embed',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_5--LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_5--LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_5--LANDINGBLOCKNODE_BUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-background-overlay-height-vh'),
		),
		'nodes' => array(
			'.landing-block-node-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_49_5--LANDINGBLOCKNODE_CONTAINER'),
				'type' => 'animation',
			),
			'.landing-block-node-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_49_5--LANDINGBLOCKNODE_BUTTON'),
				'type' => 'text-align',
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_49_5--LANDINGBLOCKNODETITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_49_5--LANDINGBLOCKNODETEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_49_5--LANDINGBLOCKNODE_BUTTON'),
				'type' => 'button',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_inline_video'),
	),
);