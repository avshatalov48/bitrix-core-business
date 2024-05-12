<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_48.2.VIDEO_BUTTON_ON_BGIMG_NAME'),
		'section' => array('cover', 'video'),
		'type' => ['page', 'store', 'smn'],
		'dynamic' => false,
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_48.2.VIDEO_BUTTON_ON_BGIMG_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_48.2.VIDEO_BUTTON_ON_BGIMG_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'link',
		),
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_48.2.VIDEO_BUTTON_ON_BGIMG_NODES_LANDINGBLOCKNODEBGIMG'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'useInDesigner' => false,
			'dimensions' => array('width' => 1920, 'height' => 1080),
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('display', 'animation'),
		),
		'nodes' => array(
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_48.2.VIDEO_BUTTON_ON_BGIMG_NODES_LANDINGBLOCKNODETEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_48.2.VIDEO_BUTTON_ON_BGIMG_NODES_LANDINGBLOCKNODEBUTTON'),
				'type' => 'background-color',
			),
			'.landing-block-node-bgimg' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_48.2.VIDEO_BUTTON_ON_BGIMG_NODES_LANDINGBLOCKNODEBGIMG'),
				'type' => ['background', 'height-vh'],
			),
		),
	),
);