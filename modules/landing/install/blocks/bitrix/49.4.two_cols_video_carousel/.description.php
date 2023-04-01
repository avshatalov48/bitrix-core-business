<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_49_4_TWO_COLS_VIDEO_CAROUSEL--NAME'),
		'section' => array('video'),
		'dynamic' => false,
		'version' => '18.5.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_4_TWO_COLS_VIDEO_CAROUSEL--LANDINGBLOCKNODECARDVIDEO'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-video' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_4_TWO_COLS_VIDEO_CAROUSEL--LANDINGBLOCKNODECARDVIDEO'),
			'type' => 'embed',
		),
	),
	'style' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_4_TWO_COLS_VIDEO_CAROUSEL--LANDINGBLOCKNODECARDVIDEO'),
			'type' => 'animation',
		),
		'.landing-block-slider' => [
			'additional' => [
				'name' => Loc::getMessage('LANDING_BLOCK_49_4_TWO_COLS_VIDEO_CAROUSEL_SLIDER'),
				'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show', 'dots'],
			]
		],
		'.landing-block-node-video-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_49_4_TWO_COLS_VIDEO_CAROUSEL--LANDINGBLOCKNODECARDVIDEO'),
			'type' => ['orientation', 'video-scale'],
		],
	),
	'assets' => array(
		'ext' => array('landing_inline_video', 'landing_carousel'),
	),
);