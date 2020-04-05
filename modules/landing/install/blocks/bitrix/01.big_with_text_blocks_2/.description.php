<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NAME'),
		'section' => array('cover'),
	),
	'cards' => array(
		'.landing-block-node-card-block' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_CARDS_LANDINGBLOCKNODECARDBLOCK'),
			'label' => array('.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDBGIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920, 'height' => 1280),
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		
		),
		'.landing-block-node-card-label-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDLABELTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-label-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDLABELTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-link1' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDLINK'),
			'type' => 'link',
		),
		'.landing-block-node-card-link2' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDLINK'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-wo-background'),
		),
		'nodes' => array(
			'.landing-block-node-card-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARDTITLE'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARDTEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-card-label-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARDLABELTITLE'),
				'type' => array('typo', 'box', 'paddings'),
			),
			'.landing-block-node-card-label-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARDLABELTEXT'),
				'type' => array('typo', 'box', 'paddings'),
			),
			'.landing-block-node-card-buttons1' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARD_BUTTONS'),
				'type' => array('animation', 'text-align'),
			),
			'.landing-block-node-card-buttons2' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARD_BUTTONS'),
				'type' => array('animation', 'text-align'),
			),
			'.landing-block-node-card-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDLINK'),
				'type' => 'button',
			),
			'.landing-block-node-card-bgimg' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDBGIMG'),
				'type' => 'background-overlay',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);