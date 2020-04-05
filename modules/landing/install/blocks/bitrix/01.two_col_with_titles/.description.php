<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NAME'),
		'section' => array('cover'),
		'dynamic' => false,
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-small-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NODES_LANDINGBLOCKNODESMALLTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'link',
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1080, 'height' => 1080),
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('display'),
		),
		'nodes' => array(
			'.landing-block-node-inner-container-left' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NODES_LANDINGBLOCKNODE_INNER_CONTAINER'),
				'type' => array('padding-top', 'padding-bottom', 'animation'),
			),
			'.landing-block-node-inner-container-right' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NODES_LANDINGBLOCKNODE_INNER_CONTAINER'),
				'type' => array('padding-top', 'padding-bottom', 'animation'),
			),
			'.landing-block-node-small-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NODES_LANDINGBLOCKNODESMALLTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NODES_LANDINGBLOCKNODETITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-title-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NODES_LANDINGBLOCKNODETITLECONTAINER'),
				'type' => 'border-color',
			),
			'.landing-block-node-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NODES_LANDINGBLOCKNODEBUTTON'),
				'type' => array('button'),
			),
			'.landing-block-node-img' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NODES_LANDINGBLOCKNODEIMG'),
				'type' => ['background-overlay', 'height-vh'],
			),
			'.landing-block-node-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_1_TWO_COL_WITH_TITLES_NODES_LANDINGBLOCKNODEBUTTON'),
				'type' => 'text-align',
			),
		),
	),
);