<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_46.1.COVER_WITH_BG_IMAGE_AND_BIG_TITLE_NAME'),
		'section' => array('cover'),
		'dynamic' => false,
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.1.COVER_WITH_BG_IMAGE_AND_BIG_TITLE_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.1.COVER_WITH_BG_IMAGE_AND_BIG_TITLE_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.1.COVER_WITH_BG_IMAGE_AND_BIG_TITLE_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.1.COVER_WITH_BG_IMAGE_AND_BIG_TITLE_NODES_LANDINGBLOCKNODEBGIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920, 'height' => 1080),
		),
	),
	'style' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.1.COVER_WITH_BG_IMAGE_AND_BIG_TITLE_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.1.COVER_WITH_BG_IMAGE_AND_BIG_TITLE_NODES_LANDINGBLOCKNODETITLE'),
			'type' => array('typo', 'border-color'),
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.1.COVER_WITH_BG_IMAGE_AND_BIG_TITLE_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.1.COVER_WITH_BG_IMAGE_AND_BIG_TITLE_NODES_LANDINGBLOCKNODEBGIMG'),
			'type' => array('background-overlay', 'background-attachment'),
		),
		'.landing-block-node-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.1.COVER_WITH_BG_IMAGE_AND_BIG_TITLE_NODES_LANDINGBLOCKNODE_CONTAINER'),
			'type' => 'animation',
		),
	),
);