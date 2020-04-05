<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NAME'),
			'section' => array('image'),
		),
	'cards' =>
		array(),
	'nodes' =>
		array(
			'.landing-block-node-subtitle1' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODESUBTITLE1'),
					'type' => 'text',
				),
			'.landing-block-node-title1' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODETITLE1'),
					'type' => 'text',
				),
			'.landing-block-node-button1' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODEBUTTON'),
					'type' => 'link',
				),
			'.landing-block-node-img1' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODEIMG1'),
					'type' => 'img',
					'dimensions' => array('width' => 800, 'height' => 867),
				),
			'.landing-block-node-subtitle2' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODESUBTITLE2'),
					'type' => 'text',
				),
			'.landing-block-node-title2' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODETITLE2'),
					'type' => 'text',
				),
			'.landing-block-node-text2' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODETEXT2'),
					'type' => 'text',
				),
			'.landing-block-node-img2' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODEIMG2'),
					'type' => 'img',
					'dimensions' => array('width' => 800, 'height' => 867),
				),
			'.landing-block-node-title-mini' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODETITLEMINI'),
					'type' => 'text',
				),
			'.landing-block-node-text-mini' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODETEXTMINI'),
					'type' => 'text',
				),
			'.landing-block-node-img-mini' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODEIMGMINI'),
					'type' => 'img',
					'dimensions' => array('width' => 800, 'height' => 401),
				),
		),
	'style' =>
		array(
			'.landing-block-node-block' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODE_BLOCK'),
					'type' => array('animation'),
				),
			'.landing-block-node-subtitle1' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODESUBTITLE1'),
					'type' => array('typo', 'border-color'),
				),
			'.landing-block-node-title1' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODETITLE1'),
					'type' => 'typo',
				),
			'.landing-block-node-subtitle2' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODESUBTITLE2'),
					'type' => 'typo',
				),
			'.landing-block-node-title2' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODETITLE2'),
					'type' => 'typo',
				),
			'.landing-block-node-button1' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODEBUTTON'),
					'type' => 'button',
				),
			'.landing-block-node-text2' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODETEXT2'),
					'type' => 'typo',
				),
			'.landing-block-node-title-mini' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODETITLEMINI'),
					'type' => 'typo',
				),
			'.landing-block-node-text-mini' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODETEXTMINI'),
					'type' => 'typo',
				),
			'.landing-block-node-img-mini' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODEIMGMINI'),
					'type' => 'typo',
				),
			'.landing-block-node-bg-mini' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODE_BGMINI'),
					'type' => array('box', 'animation'),
				),
			'.landing-block-node-button1-container' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.2.THREE_TILES_WITH_IMG_ZOOM_NODES_LANDINGBLOCKNODEBUTTON'),
					'type' => 'text-align',
				),
		),
);