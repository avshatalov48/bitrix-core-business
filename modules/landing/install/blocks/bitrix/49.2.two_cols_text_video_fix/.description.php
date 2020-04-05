<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_49_3_TWO_COLS_TEXT_VIDEO_FIX--NAME'),
		'section' => array('video'),
		'version' => '18.5.0',
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_3_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_3_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-video' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_3_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODEVIDEO'),
			'type' => 'embed',
		),
	),
	'style' => array(
		'.landing-block-node-text-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_3_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETEXT'),
			'type' => array('animation', 'align-items'),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_3_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETITLE'),
			'type' => array('typo'),
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_3_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETEXT'),
			'type' => array('typo'),
		),
		'.landing-block-node-video-col' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_3_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODEVIDEO'),
			'type' => 'animation',
		),
	),
	'assets' => array(
		'ext' => array('landing_inline_video'),
	),
);