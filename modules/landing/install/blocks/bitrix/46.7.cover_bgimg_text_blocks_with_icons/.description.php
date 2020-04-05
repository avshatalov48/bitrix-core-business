<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_NAME'),
			'section' => array('cover'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_CARDS_LANDINGBLOCKNODECARD'),
					'label' => array('.landing-block-node-card-title'),
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-bgimg' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_NODES_LANDINGBLOCKNODEBGIMG'),
					'type' => 'img',
					'dimensions' => array('width' => 1920, 'height' => 1440),
				),
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'text',
				),
			'.landing-block-node-card-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_NODES_LANDINGBLOCKNODECARDTITLE2'),
					'type' => 'text',
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_NODES_LANDINGBLOCKNODECARDTEXT2'),
					'type' => 'text',
				),
			'.landing-block-node-card-icon' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_NODES_LANDINGBLOCKNODECARDICON2'),
					'type' => 'icon',
				),
		),
	'style' =>
		array(
			'block' => array(
				'type' => array('block-default-wo-background'),
			),
			'nodes' => array(
				'.landing-block-node-container' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_NODES_LANDINGBLOCKNODE_CONTAINER'),
						'type' => 'animation',
					),
				'.landing-block-node-title' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_NODES_LANDINGBLOCKNODETITLE'),
						'type' => 'typo',
					),
				'.landing-block-node-card-title' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_NODES_LANDINGBLOCKNODECARDTITLE2'),
						'type' => 'typo',
					),
				'.landing-block-node-card-text' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_NODES_LANDINGBLOCKNODECARDTEXT2'),
						'type' => 'typo',
					),
				'.landing-block-node-card-icon-container' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_NODES_LANDINGBLOCKNODECARDICON2'),
						'type' => 'color',
					),
				'.landing-block-node-bgimg' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.7.COVER_BGIMG_TEXT_BLOCKS_WITH_ICONS_NODES_LANDINGBLOCKNODEBGIMG'),
						'type' => array('background-overlay', 'height-vh', 'background-attachment')
					),
			),

		),
);