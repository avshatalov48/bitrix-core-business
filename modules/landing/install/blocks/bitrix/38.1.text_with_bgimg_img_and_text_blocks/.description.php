<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NAME'),
		'section' => array('cover'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-icon', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODEBGIMG'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'create2xByDefault' => false,
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDICON'),
			'type' => 'icon',
		),
		'.landing-block-node-leftblock-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODELEFTBLOCKIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1200, 'height' => 811),
			'create2xByDefault' => false,
		),
		'.landing-block-node-leftblock-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODELEFTBLOCKSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-leftblock-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODELEFTBLOCKTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-leftblock-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODELEFTBLOCKTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-leftblock-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODELEFTBLOCKBUTTON'),
			'type' => 'link',
		),
		'.landing-block-node-label-left' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODELABELLEFT'),
			'type' => 'text',
		),
		'.landing-block-node-label-right' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODELABELRIGHT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'block' => array(
			'type' => ['background', 'block-default-background-overlay'],
		),
		'nodes' => array(
			'.landing-block-node-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODESUBTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODETITLE'),
				'type' => ['typo', 'heading'],
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODETEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODECARDTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODECARDTEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-leftblock-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODELEFTBLOCKSUBTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-leftblock-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODELEFTBLOCKTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-leftblock-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODELEFTBLOCKTEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-leftblock-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODELEFTBLOCKBUTTON'),
				'type' => 'button',
			),
			'.landing-block-node-label-left' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODELABELLEFT'),
				'type' => 'typo',
			),
			'.landing-block-node-label-right' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODELABELRIGHT'),
				'type' => 'typo',
			),
			'.landing-block-node-leftblock' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODELEFTBLOCK'),
				'type' => array('box', 'animation'),
			),
			'.landing-block-node-rightblock' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_STYLE_LANDINGBLOCKNODERIGHTBLOCK'),
				'type' => array('animation'),
			),
			'.landing-block-node-card-icon-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODECARDICON'),
				'type' => 'color',
			),
			'.landing-block-node-bgimg' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_38.1.TEXT_WITH_BGIMG_IMG_AND_TEXT_BLOCKS_NODES_LANDINGBLOCKNODEBGIMG'),
				'type' => 'background-attachment',
			),
		),
	
	),
);