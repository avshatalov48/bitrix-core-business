<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_16_3_TWO_COLS_MAP_TEXT_FIX--NAME'),
		'section' => array('contacts'),
		'version' => '18.5.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
		'subtype' => 'map',
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_16_3_TWO_COLS_MAP_TEXT_FIX--LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_16_3_TWO_COLS_MAP_TEXT_FIX--LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-map' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_16_3_TWO_COLS_MAP_TEXT_FIX--LANDINGBLOCKNODEMAP'),
			'type' => 'map',
		),
	),
	'style' => array(
		'.landing-block-node-text-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_16_3_TWO_COLS_MAP_TEXT_FIX--LANDINGBLOCKNODETEXT'),
			'type' => array('animation'),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_16_3_TWO_COLS_MAP_TEXT_FIX--LANDINGBLOCKNODETITLE'),
			'type' => ['typo', 'heading'],
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_16_3_TWO_COLS_MAP_TEXT_FIX--LANDINGBLOCKNODETEXT'),
			'type' => array('typo'),
		),
		'.landing-block-node-map-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_16_3_TWO_COLS_MAP_TEXT_FIX--LANDINGBLOCKNODEMAP'),
			'type' => 'animation',
		),
	),
	'assets' => array(
		'ext' => array('landing_map'),
	),
);