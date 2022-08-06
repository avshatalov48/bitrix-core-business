<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_54_COVER_SLIDER_BGVIDEO--NAME'),
		'section' => array('video', 'cover'),
		'dynamic' => false,
		'version' => '18.5.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_54_COVER_SLIDER_BGVIDEO--LANDINGBLOCKNODECARD'),
			'label' => ['.landing-block-node-title'],
		),
	),
	'nodes' => array(
		'.landing-block-node-video' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_54_COVER_SLIDER_BGVIDEO--LANDINGBLOCKNODE--VIDEO'),
			'type' => 'embed',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_54_COVER_SLIDER_BGVIDEO--LANDINGBLOCKNODECARD--TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_54_COVER_SLIDER_BGVIDEO--LANDINGBLOCKNODECARD--TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_54_COVER_SLIDER_BGVIDEO--LANDINGBLOCKNODECARD--BTN'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-background-overlay-height-vh'),
		),
		'nodes' => array(
			'.landing-block-node-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_54_COVER_SLIDER_BGVIDEO--LANDINGBLOCKNODE--CARD'),
				'type' => ['animation', 'align-self'],
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_54_COVER_SLIDER_BGVIDEO--LANDINGBLOCKNODECARD--TITLE'),
				'type' => ['typo', 'heading'],
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_54_COVER_SLIDER_BGVIDEO--LANDINGBLOCKNODECARD--TEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_54_COVER_SLIDER_BGVIDEO--LANDINGBLOCKNODECARD--BTN'),
				'type' => 'text-align',
			),
			'.landing-block-node-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_54_COVER_SLIDER_BGVIDEO--LANDINGBLOCKNODECARD--BTN'),
				'type' => 'button',
			),
			'.landing-block-slider' => [
				'additional' => [
					'name' => Loc::getMessage('LANDING_BLOCK_54_COVER_SLIDER_BGVIDEO_SLIDER'),
					'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show', 'dots'],
				]
			],
		),
	),
	'assets' => array(
		'ext' => array('landing_inline_video', 'landing_carousel'),
	),
);