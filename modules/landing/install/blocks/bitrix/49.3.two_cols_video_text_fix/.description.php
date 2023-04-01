<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--NAME'),
		'section' => array('video'),
		'dynamic' => false,
		'version' => '18.5.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-video' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODEVIDEO'),
			'type' => 'embed',
		),
	),
	'style' => array(
		'.landing-block-node-text-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETEXT'),
			'type' => array('animation', 'align-items'),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETITLE'),
			'type' => array('typo', 'heading'),
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODETEXT'),
			'type' => array('typo'),
		),
		'.landing-block-node-video-col' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODEVIDEO'),
			'type' => array('align-self', 'animation'),
		),
		'.landing-block-node-video-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_49_2_TWO_COLS_TEXT_VIDEO_FIX--LANDINGBLOCKNODEVIDEO'),
			'type' => ['orientation', 'video-scale'],
		],
	),
	'assets' => array(
		'ext' => array('landing_inline_video'),
	),
);